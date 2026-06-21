<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Skill;
use App\Models\Experience;
use App\Models\Certificate;
use App\Models\Setting;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function export()
    {
        $data = [
            'projects' => Project::all(),
            'skills' => Skill::all(),
            'experiences' => Experience::all(),
            'certificates' => Certificate::all(),
            'settings' => Setting::all(),
            'inquiries' => Inquiry::all(),
            'exported_at' => now()->toIso8601String(),
            'version' => '1.0'
        ];

        return Response::json($data, 200, [
            'Content-Disposition' => 'attachment; filename="portfolio-backup-' . now()->format('Y-m-d-His') . '.json"'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup' => 'required|file|mimes:json'
        ]);

        $file = $request->file('backup');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !isset($data['version'])) {
            return response()->json(['message' => 'Invalid backup file'], 400);
        }

        // Validate required fields
        $requiredFields = ['projects', 'skills', 'experiences', 'certificates', 'settings', 'inquiries'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || !is_array($data[$field])) {
                return response()->json(['message' => "Invalid backup file: missing or invalid '{$field}' field"], 400);
            }
        }

        // Use database transaction for atomic import
        \DB::transaction(function () use ($data) {
            // Clear existing data
            Project::truncate();
            Skill::truncate();
            Experience::truncate();
            Certificate::truncate();
            Setting::truncate();
            Inquiry::truncate();

            // Import data
            foreach ($data['projects'] as $project) {
                Project::create((array) $project);
            }

            foreach ($data['skills'] as $skill) {
                Skill::create((array) $skill);
            }

            foreach ($data['experiences'] as $experience) {
                Experience::create((array) $experience);
            }

            foreach ($data['certificates'] as $certificate) {
                Certificate::create((array) $certificate);
            }

            foreach ($data['settings'] as $setting) {
                Setting::create((array) $setting);
            }

            foreach ($data['inquiries'] as $inquiry) {
                Inquiry::create((array) $inquiry);
            }
        });

        return response()->json(['message' => 'Backup imported successfully']);
    }
}
