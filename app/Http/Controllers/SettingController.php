<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SettingController extends Controller
{
    // Public endpoint
    public function index()
    {
        $settings = Setting::firstOrCreate(
            [],
            [
                'site_name' => 'Portofolio Owner',
                'tagline' => 'Informatics Student',
                'name' => 'Portofolio Owner',
                'title' => 'Informatics Student',
                'email' => '',
                'whatsapp' => '',
                'bio' => '',
                'about_me' => "Aku Deva, mahasiswa Informatika yang antusias dalam pengembangan web, Internet of Things, dan pengolahan data. Aku suka eksplor teknologi baru dan membangun project yang bukan cuma terlihat rapi, tapi juga punya alur yang jelas di belakangnya.\n\nSaat ini aku banyak belajar Laravel, Flutter, ESP32, dan data analysis. Aku terbiasa bekerja bertahap: memahami kebutuhan, membuat versi kecil, lalu memperbaiki detailnya sampai lebih nyaman digunakan.",
                'github' => '',
                'linkedin' => '',
                'instagram' => '',
                'completed_projects' => 0,
                'tech_stack_count' => 0,
                'gpa' => '',
                'auto_update_skill_badges' => true,
                'show_learning_curve' => true,
                'show_github_activity' => true,
            ]
        );

        return response()->json([
            'data' => [
                'id' => $settings?->id,
                'name' => $settings?->name ?? $settings?->site_name,
                'title' => $settings?->title ?? $settings?->tagline,
                'email' => $settings?->email,
                'whatsapp' => $settings?->whatsapp,
                'github' => $settings?->github,
                'linkedin' => $settings?->linkedin,
                'instagram' => $settings?->instagram,
                'bio' => $settings?->bio,
                'aboutMe' => $settings?->about_me,
                'gpa' => $settings?->gpa,
                'completedProjects' => $settings?->completed_projects,
                'techStackCount' => $settings?->tech_stack_count,
                'autoUpdateSkillBadges' => $settings?->auto_update_skill_badges ?? true,
                'showLearningCurve' => $settings?->show_learning_curve ?? true,
                'showGitHubActivity' => $settings?->show_github_activity ?? true,
                'profileImage' => $settings?->profile_image ? (Str::startsWith($settings->profile_image, ['http://', 'https://']) ? $settings->profile_image : asset('storage/' . $settings->profile_image)) : null,
                'resumeUrl' => $settings?->resume ? (Str::startsWith($settings->resume, ['http://', 'https://']) ? $settings->resume : url('/api/resume')) : null,
                'metaTitle' => $settings?->meta_title,
                'metaDescription' => $settings?->meta_description,
                'metaKeywords' => $settings?->meta_keywords,
                'ogTitle' => $settings?->og_title,
                'ogDescription' => $settings?->og_description,
                'ogImage' => $settings?->og_image,
            ],
        ]);
    }

    // Serve resume with inline disposition to allow browser rendering
    public function resume()
    {
        $settings = Setting::first();
        if (!$settings || !$settings->resume) {
            return response()->json(['message' => 'Resume not found'], 404);
        }

        $path = storage_path('app/public/' . $settings->resume);
        if (!file_exists($path)) {
            return response()->json(['message' => 'Resume file missing'], 404);
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    // Admin endpoints
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'about_me' => 'nullable|string',
            'profile_image' => 'nullable',
            'resume' => 'nullable',
            'github' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'instagram' => 'nullable|url',
            'completed_projects' => 'nullable|integer|min:0',
            'tech_stack_count' => 'nullable|integer|min:0',
            'gpa' => 'nullable|string|max:10',
            'auto_update_skill_badges' => 'nullable|boolean',
            'show_learning_curve' => 'nullable|boolean',
            'show_github_activity' => 'nullable|boolean',
            'site_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string',
        ]);

        if (empty($validated['name']) && !empty($validated['site_name'])) {
            $validated['name'] = $validated['site_name'];
        }

        if (empty($validated['title']) && !empty($validated['tagline'])) {
            $validated['title'] = $validated['tagline'];
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('settings/profile', 'public');
        }

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $uploadedPath = $request->file('resume')->store('settings/resume', 'public');
            $validated['resume'] = $uploadedPath;

            // If a document type was uploaded, try convert to PDF using LibreOffice (soffice)
            $ext = strtolower($request->file('resume')->getClientOriginalExtension() ?? '');
            if (in_array($ext, ['doc', 'docx', 'odt'])) {
                try {
                    $fullPath = storage_path('app/public/' . $uploadedPath);
                    $outDir = dirname($fullPath);
                    $base = pathinfo($fullPath, PATHINFO_FILENAME);

                    $process = new Process(['soffice', '--headless', '--convert-to', 'pdf', '--outdir', $outDir, $fullPath]);
                    $process->setTimeout(120);
                    $process->run();

                    if ($process->isSuccessful()) {
                        $pdfName = $base . '.pdf';
                        $pdfRel = dirname($uploadedPath) . '/' . $pdfName;
                        // Remove original uploaded doc and use PDF instead
                        Storage::disk('public')->delete($uploadedPath);
                        $validated['resume'] = $pdfRel;
                    } else {
                        // leave original uploaded file if conversion failed
                        // optionally log
                        // 
                    }
                } catch (ProcessFailedException $e) {
                    // conversion failed; keep original file
                } catch (\Throwable $e) {
                    // ignore conversion errors and keep original
                }
            }
        }

        $setting = Setting::create($validated);

        return response()->json([
            'message' => 'Settings created successfully',
            'data' => $setting,
        ], 201);
    }

    public function show(Setting $setting)
    {
        return response()->json([
            'data' => $setting,
        ]);
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'about_me' => 'nullable|string',
            'profile_image' => 'nullable',
            'resume' => 'nullable',
            'github' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'instagram' => 'nullable|url',
            'completed_projects' => 'nullable|integer|min:0',
            'tech_stack_count' => 'nullable|integer|min:0',
            'gpa' => 'nullable|string|max:10',
            'auto_update_skill_badges' => 'nullable|boolean',
            'show_learning_curve' => 'nullable|boolean',
            'show_github_activity' => 'nullable|boolean',
            'site_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string',
        ]);

        if (empty($validated['name']) && !empty($validated['site_name'])) {
            $validated['name'] = $validated['site_name'];
        }

        if (empty($validated['title']) && !empty($validated['tagline'])) {
            $validated['title'] = $validated['tagline'];
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($setting->profile_image) {
                Storage::disk('public')->delete($setting->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('settings/profile', 'public');
        }

        // Handle resume upload
        if ($request->hasFile('resume')) {
            // Delete old resume
            if ($setting->resume) {
                Storage::disk('public')->delete($setting->resume);
            }
            $uploadedPath = $request->file('resume')->store('settings/resume', 'public');
            $validated['resume'] = $uploadedPath;

            // Convert to PDF if uploaded a document
            $ext = strtolower($request->file('resume')->getClientOriginalExtension() ?? '');
            if (in_array($ext, ['doc', 'docx', 'odt'])) {
                try {
                    $fullPath = storage_path('app/public/' . $uploadedPath);
                    $outDir = dirname($fullPath);
                    $base = pathinfo($fullPath, PATHINFO_FILENAME);

                    $process = new Process(['soffice', '--headless', '--convert-to', 'pdf', '--outdir', $outDir, $fullPath]);
                    $process->setTimeout(120);
                    $process->run();

                    if ($process->isSuccessful()) {
                        $pdfName = $base . '.pdf';
                        $pdfRel = dirname($uploadedPath) . '/' . $pdfName;
                        Storage::disk('public')->delete($uploadedPath);
                        $validated['resume'] = $pdfRel;
                    }
                } catch (ProcessFailedException $e) {
                    // keep original if conversion fails
                } catch (\Throwable $e) {
                    // keep original
                }
            }
        }

        $setting->update($validated);

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $setting,
        ]);
    }

    public function destroy(Setting $setting)
    {
        // Delete profile image
        if ($setting->profile_image) {
            Storage::disk('public')->delete($setting->profile_image);
        }

        // Delete resume
        if ($setting->resume) {
            Storage::disk('public')->delete($setting->resume);
        }

        $setting->delete();

        return response()->json([
            'message' => 'Settings deleted successfully',
        ]);
    }
}
