<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Support\ApprovingAuthorities;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

/**
 * Name-only settings for the CS Form No. 6 approving authorities that do not
 * sign through the system: the 7.A Authorized Officer and the School Division
 * Superintendent. Each role is read and saved on its own.
 */
class ApprovingAuthorityController extends Controller
{
    public function showOfficer()
    {
        return $this->show(ApprovingAuthorities::OFFICER_KEY);
    }

    public function updateOfficer(Request $request)
    {
        return $this->update(
            $request,
            ApprovingAuthorities::OFFICER_KEY,
            '7.A Authorized Officer',
            'Authorized officer name updated successfully.'
        );
    }

    public function showSuperintendent()
    {
        return $this->show(ApprovingAuthorities::SUPERINTENDENT_KEY);
    }

    public function updateSuperintendent(Request $request)
    {
        return $this->update(
            $request,
            ApprovingAuthorities::SUPERINTENDENT_KEY,
            'School Division Superintendent',
            'School Division Superintendent name updated successfully.'
        );
    }

    private function show(string $key)
    {
        return response()->json([
            'name' => ApprovingAuthorities::get($key),
        ]);
    }

    private function update(Request $request, string $key, string $label, string $message)
    {
        // Trim and collapse repeated whitespace before validating.
        $name = $request->input('name');

        if (is_string($name)) {
            $request->merge([
                'name' => trim((string) preg_replace('/\s+/u', ' ', $name)),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'not_regex:/[\x00-\x1F\x7F]/'],
        ]);

        $previous = ApprovingAuthorities::get($key);

        if ($previous !== $validated['name']) {
            ApprovingAuthorities::set($key, $validated['name']);

            AuditLogger::log(
                $label . ' updated',
                'Updated the ' . $label . ' name' .
                    ($previous !== '' ? ' from "' . $previous . '"' : '') .
                    ' to "' . $validated['name'] . '".'
            );
        }

        return response()->json([
            'message' => $message,
            'data' => [
                'name' => $validated['name'],
            ],
        ]);
    }
}
