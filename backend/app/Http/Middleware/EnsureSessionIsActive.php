<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();
        if (!$token instanceof PersonalAccessToken) {
            return response()->json(['message' => 'Please sign in again.'], 401);
        }

        if ($request->isMethod('POST') && $request->is('api/session/activity')) {
            $request->validate(['idle_ms' => 'required|integer|min:0|max:599999']);
        }

        // Separate from Sanctum last_used_at: background API calls must not
        // extend the user's inactivity deadline. Lock prevents revival races.
        $active = DB::transaction(function () use ($token, $request) {
            $row = DB::table('personal_access_tokens')->where('id', $token->getKey())
                ->lockForUpdate()->first();
            if (!$row) return false;
            $lastActivity = $row->last_activity_at;
            if (!$lastActivity || now()->greaterThanOrEqualTo(
                \Carbon\Carbon::parse($lastActivity)->addMinutes(10)
            )) {
                DB::table('personal_access_tokens')->where('id', $row->id)->delete();
                return false;
            }
            if ($request->isMethod('POST') && $request->is('api/session/activity')) {
                $activity = now()->subMilliseconds((int) $request->input('idle_ms'));
                if ($activity->greaterThan(\Carbon\Carbon::parse($lastActivity))) {
                    DB::table('personal_access_tokens')->where('id', $row->id)
                        ->update(['last_activity_at' => $activity]);
                }
            }
            return true;
        });

        if (!$active) {
            return response()->json([
                'message' => 'Your session has expired. Please sign in again.',
                'code' => 'SESSION_EXPIRED',
            ], 401);
        }
        return $next($request);
    }
}
