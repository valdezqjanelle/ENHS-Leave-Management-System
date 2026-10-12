<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRecord;
use App\Models\AdminProfile;
use App\Support\AuditLogger;
use App\Support\ProfilePhotoStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilePhotoController extends Controller
{
    private function profile(Request $request, bool $lock = false)
    {
        $query = $request->user()->role === 'admin'
            ? AdminProfile::where('user_id', $request->user()->user_id)
            : EmployeeRecord::where('user_id', $request->user()->user_id);
        if ($lock) $query->lockForUpdate();
        return $query->firstOrFail();
    }

    public function show(Request $request)
    {
        $profile = $this->profile($request);
        return response()->json([
            'photo_url' => ProfilePhotoStorage::url($profile->profile_photo_path),
            'has_photo' => (bool) $profile->profile_photo_path,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4096,max_height=4096']);
        $newPath = null;
        try {
            $oldPath = DB::transaction(function () use ($request, &$newPath) {
                $employee = $this->profile($request, true);
                $file = $request->file('photo');
                $newPath = 'profile-photos/'.$request->user()->role.'/'.$request->user()->user_id.'/'.Str::uuid().'.'.$file->extension();
                $stream = fopen($file->getRealPath(), 'rb');
                try {
                    $stored = Storage::disk('supabase')->put($newPath, $stream, ['ContentType' => $file->getMimeType()]);
                } finally {
                    if (is_resource($stream)) fclose($stream);
                }
                if (!$stored) throw new \RuntimeException('Photo upload failed.');
                $oldPath = $employee->profile_photo_path;
                $employee->profile_photo_path = $newPath;
                $employee->save();
                AuditLogger::log('Profile photo updated', "Updated profile photo for account #{$request->user()->user_id}");
                return $oldPath;
            });
        } catch (\Throwable $error) {
            ProfilePhotoStorage::remove($newPath);
            if ($error instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) throw $error;
            report($error);
            return response()->json(['message' => 'Unable to save your photo. Please try again.'], 503);
        }
        $cleaned = ProfilePhotoStorage::remove($oldPath);
        return response()->json([
            'message' => 'Profile photo saved.',
            'photo_url' => ProfilePhotoStorage::url($newPath),
            'has_photo' => true,
            'cleanup_pending' => !$cleaned,
        ]);
    }

    public function destroy(Request $request)
    {
        $oldPath = DB::transaction(function () use ($request) {
            $employee = $this->profile($request, true);
            $oldPath = $employee->profile_photo_path;
            $employee->profile_photo_path = null;
            $employee->save();
            if ($oldPath) AuditLogger::log('Profile photo removed', "Removed profile photo for account #{$request->user()->user_id}");
            return $oldPath;
        });
        $cleaned = ProfilePhotoStorage::remove($oldPath);
        return response()->json(['message' => 'Profile photo removed.', 'photo_url' => null, 'has_photo' => false, 'cleanup_pending' => !$cleaned]);
    }
}
