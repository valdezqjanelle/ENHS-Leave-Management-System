<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(string $action, string $description, ?int $userId = null): void
    {
        $userId = $userId ?? Auth::id();

        if (! $userId) {
            return;
        }

        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
        ]);
    }
}