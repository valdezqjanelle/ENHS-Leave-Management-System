<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveSetting;
use Illuminate\Http\Request;

class LeaveSettingController extends Controller
{
    public function index()
    {
        $settings = LeaveSetting::first();

        if (!$settings) {
            $settings = LeaveSetting::create([
                'minimum_notice_days' => 0,
                'require_documents' => false,
                'max_consecutive_days' => 0,
                'general_policy' => null,
            ]);
        }

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'minimum_notice_days' => 'required|integer|min:0',
            'require_documents' => 'required|boolean',
            'max_consecutive_days' => 'required|integer|min:0',
            'general_policy' => 'nullable|string',
        ]);

        $settings = LeaveSetting::first();

        if (!$settings) {
            $settings = LeaveSetting::create($validated);
        } else {
            $settings->update($validated);
        }

        return response()->json([
            'message' => 'Leave rules updated successfully.',
            'settings' => $settings,
        ]);
    }
}