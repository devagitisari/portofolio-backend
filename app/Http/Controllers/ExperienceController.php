<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    // Public endpoint
    public function index()
    {
        $experiences = Experience::orderBy('start_date', 'desc')->get();

        return response()->json([
            'data' => $experiences,
        ]);
    }

    // Admin endpoints
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'certificate' => 'nullable',
        ]);

        // If is_current is true, set end_date to null
        if ($validated['is_current'] ?? false) {
            $validated['end_date'] = null;
        }

        if ($request->hasFile('certificate')) {
            $validated['certificate'] = $request->file('certificate')->store('experiences/certificates', 'public');
        }

        $experience = Experience::create($validated);

        return response()->json([
            'message' => 'Experience created successfully',
            'data' => $experience,
        ], 201);
    }

    public function show(Experience $experience)
    {
        return response()->json([
            'data' => $experience,
        ]);
    }

    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'certificate' => 'nullable',
        ]);

        // If is_current is true, set end_date to null
        if ($validated['is_current'] ?? false) {
            $validated['end_date'] = null;
        }

        if ($request->hasFile('certificate')) {
            if ($experience->certificate && !str_starts_with($experience->certificate, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($experience->certificate);
            }
            $validated['certificate'] = $request->file('certificate')->store('experiences/certificates', 'public');
        }

        $experience->update($validated);

        return response()->json([
            'message' => 'Experience updated successfully',
            'data' => $experience,
        ]);
    }

    public function destroy(Experience $experience)
    {
        if ($experience->certificate && !str_starts_with($experience->certificate, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($experience->certificate);
        }

        $experience->delete();

        return response()->json([
            'message' => 'Experience deleted successfully',
        ]);
    }
}
