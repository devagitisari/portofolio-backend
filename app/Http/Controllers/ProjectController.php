<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    // Public endpoints
    public function index()
    {
        $projects = Project::with(['images', 'skills'])
            ->where('status', 'published')
            ->orderBy('featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return ProjectResource::collection($projects);
    }

    public function show(string $slug)
    {
        $project = Project::with(['images', 'skills'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return new ProjectResource($project);
    }

    // Admin endpoints
    public function store(Request $request)
    {
        // Decode skill_ids if sent as JSON string
        if ($request->has('skill_ids') && is_string($request->input('skill_ids'))) {
            $request->merge(['skill_ids' => json_decode($request->input('skill_ids'), true) ?? []]);
        }

        foreach (['key_features', 'image_urls'] as $arrayField) {
            if ($request->has($arrayField) && is_string($request->input($arrayField))) {
                $request->merge([$arrayField => json_decode($request->input($arrayField), true) ?? []]);
            }
        }

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'slug'           => 'nullable|string|unique:projects,slug',
            'description'    => 'required|string',
            'long_description' => 'nullable|string',
            'project_role'   => 'nullable|string|max:255',
            'problem'        => 'nullable|string',
            'solution'       => 'nullable|string',
            'key_features'   => 'nullable|array',
            'key_features.*' => 'nullable|string',
            'impact'         => 'nullable|string',
            'skill_ids'      => 'nullable|array',
            'skill_ids.*'    => 'nullable|integer|exists:skills,id',
            'category'       => 'nullable|string|max:255',
            'status'         => 'nullable|in:draft,published',
            'thumbnail'      => 'nullable',
            'demo_url'       => 'nullable|url',
            'github_url'     => 'nullable|url',
            'featured'       => 'nullable|boolean',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'image_urls'     => 'nullable|array',
            'image_urls.*'   => 'nullable|url',
            'images.*'       => 'nullable|image|max:2048',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Extract skill_ids before creating (not a DB column)
        $skillIds = $validated['skill_ids'] ?? [];
        unset($validated['skill_ids']);
        $imageUrls = $validated['image_urls'] ?? [];
        unset($validated['image_urls']);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('projects/thumbnails', 'public');
        }

        $project = Project::create($validated);

        // Sync skills
        $project->skills()->sync($skillIds);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('projects/images', 'public');
                $project->images()->create(['image' => $path]);
            }
        }

        foreach ($imageUrls as $url) {
            if ($url) {
                $project->images()->create(['image' => $url]);
            }
        }

        return response()->json([
            'message' => 'Project created successfully',
            'data'    => new ProjectResource($project->load(['images', 'skills'])),
        ], 201);
    }

    public function update(Request $request, Project $project)
    {
        // Decode skill_ids if sent as JSON string
        if ($request->has('skill_ids') && is_string($request->input('skill_ids'))) {
            $request->merge(['skill_ids' => json_decode($request->input('skill_ids'), true) ?? []]);
        }

        foreach (['key_features', 'image_urls'] as $arrayField) {
            if ($request->has($arrayField) && is_string($request->input($arrayField))) {
                $request->merge([$arrayField => json_decode($request->input($arrayField), true) ?? []]);
            }
        }

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'slug'           => 'nullable|string|unique:projects,slug,' . $project->id,
            'description'    => 'required|string',
            'long_description' => 'nullable|string',
            'project_role'   => 'nullable|string|max:255',
            'problem'        => 'nullable|string',
            'solution'       => 'nullable|string',
            'key_features'   => 'nullable|array',
            'key_features.*' => 'nullable|string',
            'impact'         => 'nullable|string',
            'skill_ids'      => 'nullable|array',
            'skill_ids.*'    => 'nullable|integer|exists:skills,id',
            'category'       => 'nullable|string|max:255',
            'status'         => 'nullable|in:draft,published',
            'thumbnail'      => 'nullable',
            'demo_url'       => 'nullable|url',
            'github_url'     => 'nullable|url',
            'featured'       => 'nullable|boolean',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'image_urls'     => 'nullable|array',
            'image_urls.*'   => 'nullable|url',
            'images.*'       => 'nullable|image|max:2048',
        ]);

        // Extract skill_ids before updating (not a DB column)
        $skillIds = $validated['skill_ids'] ?? null;
        unset($validated['skill_ids']);
        $imageUrls = $validated['image_urls'] ?? [];
        unset($validated['image_urls']);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            if ($project->thumbnail && !str_starts_with($project->thumbnail, 'http')) {
                Storage::disk('public')->delete($project->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('projects/thumbnails', 'public');
        }

        $project->update($validated);

        // Sync skills only if skill_ids was provided in request
        if ($skillIds !== null) {
            $project->skills()->sync($skillIds);
        }

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('projects/images', 'public');
                $project->images()->create(['image' => $path]);
            }
        }

        foreach ($imageUrls as $url) {
            if ($url) {
                $project->images()->create(['image' => $url]);
            }
        }

        return response()->json([
            'message' => 'Project updated successfully',
            'data'    => new ProjectResource($project->load(['images', 'skills'])),
        ]);
    }

    public function destroy(Project $project)
    {
        // Delete thumbnail
        if ($project->thumbnail && !str_starts_with($project->thumbnail, 'http')) {
            Storage::disk('public')->delete($project->thumbnail);
        }

        // Delete all project images
        foreach ($project->images as $image) {
            if ($image->image && !str_starts_with($image->image, 'http')) {
                Storage::disk('public')->delete($image->image);
            }
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ]);
    }
}
