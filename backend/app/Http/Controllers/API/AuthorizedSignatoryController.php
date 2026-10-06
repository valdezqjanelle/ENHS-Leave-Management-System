<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedSignatory;
use App\Models\LeaveApplication;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthorizedSignatoryController extends Controller
{
    public function show()
    {
        $signatory = AuthorizedSignatory::query()->find(1);

        return response()->json([
            'name' => $signatory?->name ?? '',
            'designation' => $signatory?->designation ?? '',
            'has_signature' => (bool) $signatory?->signature_path,
        ]);
    }

    public function signature()
    {
        $path = AuthorizedSignatory::query()->find(1)?->signature_path;
        $disk = Storage::disk('supabase');

        if (!$path || !$disk->exists($path)) {
            return response()->json([
                'message' => 'No authorized signatory signature is available.',
            ], 404);
        }

        $stream = $disk->readStream($path);

        if ($stream === false) {
            return response()->json([
                'message' => 'Unable to read the authorized signatory signature.',
            ], 500);
        }

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $extension = $mimeType === 'image/jpeg' ? 'jpg' : 'png';

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="authorized-signature.' . $extension . '"',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048|dimensions:max_width=3000,max_height=2000',
            'remove_signature' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('signature') && $request->boolean('remove_signature')) {
            throw ValidationException::withMessages([
                'signature' => 'Choose a replacement signature or remove the current one, not both.',
            ]);
        }

        $disk = Storage::disk('supabase');
        $newPath = null;

        if ($request->hasFile('signature')) {
            $newPath = $request->file('signature')->store('authorized-signatories', 'supabase');

            if (!$newPath) {
                throw new \RuntimeException('The authorized signatory signature could not be stored.');
            }
        }

        $oldPath = null;

        try {
            DB::transaction(function () use ($request, $validated, $newPath, &$oldPath) {
                $signatory = AuthorizedSignatory::query()
                    ->lockForUpdate()
                    ->find(1);

                if (!$signatory) {
                    $signatory = new AuthorizedSignatory();
                    $signatory->id = 1;
                }

                $oldPath = $signatory->signature_path;
                $signatory->name = $validated['name'];
                $signatory->designation = $validated['designation'];
                $signatory->updated_by = $request->user()->user_id;

                if ($newPath) {
                    $signatory->signature_path = $newPath;
                } elseif ($request->boolean('remove_signature')) {
                    $signatory->signature_path = null;
                }

                $signatory->save();
            });
        } catch (\Throwable $exception) {
            if ($newPath) {
                try {
                    $disk->delete($newPath);
                } catch (\Throwable $cleanupException) {
                    Log::warning('Unable to remove an unused authorized signatory upload after a failed save.', [
                        'path' => $newPath,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }
            }

            throw $exception;
        }

        $signatory = AuthorizedSignatory::query()->findOrFail(1);
        $signatureChanged = $oldPath !== $signatory->signature_path;

        if (
            $signatureChanged &&
            $oldPath &&
            !LeaveApplication::query()
                ->where('signatory_signature_path_snapshot', $oldPath)
                ->exists()
        ) {
            try {
                if (!$disk->delete($oldPath)) {
                    Log::warning('Unable to remove a replaced authorized signatory signature.', [
                        'path' => $oldPath,
                    ]);
                }
            } catch (\Throwable $cleanupException) {
                Log::warning('Unable to remove a replaced authorized signatory signature.', [
                    'path' => $oldPath,
                    'error' => $cleanupException->getMessage(),
                ]);
            }
        }

        AuditLogger::log(
            'Authorized signatory updated',
            'Updated the authorized signatory details' .
                ($signatureChanged ? ' and signature.' : '.')
        );

        return response()->json([
            'message' => 'Authorized signatory settings updated successfully.',
            'data' => [
                'name' => $signatory->name,
                'designation' => $signatory->designation,
                'has_signature' => (bool) $signatory->signature_path,
            ],
        ]);
    }
}
