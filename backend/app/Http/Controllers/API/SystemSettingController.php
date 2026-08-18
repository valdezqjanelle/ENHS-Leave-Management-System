<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all();

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:255',
            'school_name' => 'required|string|max:255',
            'system_description' => 'nullable|string',
            'about_us' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:50',
            'system_version' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value ?? '']
            );
        }

        return response()->json([
            'message' => 'System settings updated successfully.',
            'settings' => SystemSetting::all(),
        ]);
    }
}