<?php

namespace App\Http\Controllers;

use App\Models\ApprovalSetting;
use Illuminate\Http\Request;

class ApprovalSettingController extends Controller
{
    public function index()
    {
        $settings = ApprovalSetting::first();

        if (!$settings) {
            $settings = ApprovalSetting::create([
                'require_admin_review' => true,
                'require_principal_endorsement' => true,
                'allow_admin_remarks' => true,
                'auto_update_status' => true,
                'auto_update_balance' => true,
            ]);
        }

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'require_admin_review' => ['required', 'boolean'],
            'require_principal_endorsement' => ['required', 'boolean'],
            'allow_admin_remarks' => ['required', 'boolean'],
            'auto_update_status' => ['required', 'boolean'],
            'auto_update_balance' => ['required', 'boolean'],
        ]);

        $settings = ApprovalSetting::first();

        if (!$settings) {
            $settings = ApprovalSetting::create($validated);
        } else {
            $settings->update($validated);
        }

        return response()->json([
            'message' => 'Approval settings updated successfully.',
            'data' => $settings,
        ]);
    }
}