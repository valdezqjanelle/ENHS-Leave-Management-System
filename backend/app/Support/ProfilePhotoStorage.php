<?php
namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ProfilePhotoStorage
{
    public static function url(?string $path): ?string
    {
        if (!$path) return null;
        try {
            return Storage::disk('supabase')->temporaryUrl($path, now()->addHour());
        } catch (\Throwable $error) {
            report($error);
            return null;
        }
    }

    public static function remove(?string $path): bool
    {
        if (!$path) return true;
        try {
            return Storage::disk('supabase')->delete($path);
        } catch (\Throwable $error) {
            report($error);
            return false;
        }
    }
}
