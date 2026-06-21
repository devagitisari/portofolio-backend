<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Setting;
use App\Notifications\NewInquiryNotification;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    // Public endpoint - Contact form
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        if (empty($validated['subject'])) {
            $validated['subject'] = 'Website Inquiry';
        }

        if (empty($validated['category'])) {
            $validated['category'] = 'General';
        }

        $inquiry = Inquiry::create($validated);

        // Send notification to admin
        $settings = Setting::first();
        if ($settings && $settings->email) {
            $settings->notify(new NewInquiryNotification($inquiry));
        }

        return response()->json([
            'message' => 'Your message has been sent successfully. We will get back to you soon!',
            'data' => $inquiry,
        ], 201);
    }

    // Admin endpoints
    public function index()
    {
        $inquiries = Inquiry::orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $inquiries,
        ]);
    }

    public function show(Inquiry $inquiry)
    {
        // Mark as read when viewed
        if (!$inquiry->is_read) {
            $inquiry->update(['is_read' => true]);
        }

        return response()->json([
            'data' => $inquiry,
        ]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'is_read' => 'nullable|boolean',
            'is_replied' => 'nullable|boolean',
        ]);

        $inquiry->update($validated);

        return response()->json([
            'message' => 'Inquiry updated successfully',
            'data' => $inquiry,
        ]);
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return response()->json([
            'message' => 'Inquiry deleted successfully',
        ]);
    }
}
