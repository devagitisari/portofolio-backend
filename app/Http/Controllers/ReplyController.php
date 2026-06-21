<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReply;
use App\Models\Inquiry;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReplyController extends Controller
{
    public function store(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        // Create reply record
        $reply = Reply::create([
            'inquiry_id' => $inquiry->id,
            'message' => $validated['message'],
            'sent_at' => now(),
        ]);

        // Mark inquiry as replied
        $inquiry->update(['is_replied' => true]);

        // Send email
        try {
            Mail::to($inquiry->email)->send(new InquiryReply($inquiry, $validated['message']));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::error('Failed to send reply email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Reply sent successfully',
            'data' => $reply,
        ], 201);
    }

    public function index(Inquiry $inquiry)
    {
        $replies = $inquiry->replies()->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $replies]);
    }
}
