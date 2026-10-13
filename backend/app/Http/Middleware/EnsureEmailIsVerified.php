<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        // Existing tokens cannot bypass verification after rollout.
        if (!$request->user()?->email_verified_at && !$request->is('api/logout')) {
            $token = $request->user()?->currentAccessToken();
            if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) $token->delete();
            return response()->json([
                'message' => 'Please sign in again to verify your email address.',
                'code' => 'EMAIL_VERIFICATION_REQUIRED',
            ], 401);
        }
        return $next($request);
    }
}
