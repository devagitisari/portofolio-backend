<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    // Public endpoint
    public function index()
    {
        $skills = Skill::withCount('projects')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Calculate percentage based on skill usage relative to total used skills
        $usedSkillsCount = $skills->filter(fn($s) => $s->projects_count > 0)->count();

        $skills = $skills->map(function ($skill) use ($usedSkillsCount) {
            if ($usedSkillsCount > 0 && $skill->projects_count > 0) {
                $calculatedPercentage = (int) round((100 / $usedSkillsCount));
            } else {
                $calculatedPercentage = 0;
            }

            return [
                'id'                   => $skill->id,
                'name'                 => $skill->name,
                'category'             => $skill->category,
                'percentage'           => $skill->percentage,
                'calculated_percentage' => $calculatedPercentage,
                'project_count'        => $skill->projects_count,
                'show_on_home'         => $skill->show_on_home,
            ];
        });

        return response()->json([
            'data' => $skills,
        ]);
    }

    // Admin endpoints
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|string|max:255',
            'percentage'   => 'required|integer|min:0|max:100',
            'show_on_home' => 'nullable|boolean',
        ]);

        $validated['show_on_home'] = $request->boolean('show_on_home');

        $skill = Skill::create($validated);

        // Calculate and save calculated percentage
        $allSkills = Skill::all();
        $usedSkillsCount = $allSkills->filter(fn($s) => $s->projects()->count() > 0)->count();
        $projectCount = $skill->projects()->count();
        
        if ($usedSkillsCount > 0 && $projectCount > 0) {
            $calculatedPercentage = (int) round((100 / $usedSkillsCount));
        } else {
            $calculatedPercentage = 0;
        }

        $skill->update([
            'calculated_percentage' => $calculatedPercentage,
            'project_count' => $projectCount,
        ]);

        return response()->json([
            'message' => 'Skill created successfully',
            'data'    => $skill,
        ], 201);
    }

    public function show(Skill $skill)
    {
        return response()->json([
            'data' => $skill,
        ]);
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|string|max:255',
            'percentage'   => 'required|integer|min:0|max:100',
        ]);

        $validated['show_on_home'] = $request->boolean('show_on_home');

        $skill->update($validated);

        // Recalculate and save calculated percentage
        $allSkills = Skill::all();
        $usedSkillsCount = $allSkills->filter(fn($s) => $s->projects()->count() > 0)->count();
        $projectCount = $skill->projects()->count();
        
        if ($usedSkillsCount > 0 && $projectCount > 0) {
            $calculatedPercentage = (int) round((100 / $usedSkillsCount));
        } else {
            $calculatedPercentage = 0;
        }

        $skill->update([
            'calculated_percentage' => $calculatedPercentage,
            'project_count' => $projectCount,
        ]);

        return response()->json([
            'message' => 'Skill updated successfully',
            'data'    => $skill,
        ]);
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return response()->json([
            'message' => 'Skill deleted successfully',
        ]);
    }
}
