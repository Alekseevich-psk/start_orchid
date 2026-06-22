<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        Feedback::create([
            'type' => 'feedback',
            'source' => 'site',
            'form_id' => 'main-feedback-form',
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'fields' => $request->except(['_token', 'name', 'email', 'phone', 'cop', 'message']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'meta' => [
                'page' => url()->previous(),
            ],
            'user_id' => auth()->id(),
            'sent_at' => now(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Спасибо! Ваша заявка отправлена.',
            ]);
        }

        return back()->with('success', 'Спасибо! Ваша заявка отправлена.');
    }
}
