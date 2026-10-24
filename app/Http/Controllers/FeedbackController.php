<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'nullable|string|max:1000',
        ]);

        // Store feedback (you can create a Feedback model later)
        // For now, just log it
        \Log::info('Feedback submitted', [
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!'
        ]);
    }
}