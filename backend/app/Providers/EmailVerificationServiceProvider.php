<?php
namespace App\Providers;

use App\Models\User;
use App\Observers\UserEmailVerificationObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class EmailVerificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::observe(UserEmailVerificationObserver::class);
        RateLimiter::for('verification-login', fn (Request $request) => [
            Limit::perMinute(10)->by('login-email:'.hash('sha256', strtolower(trim((string) $request->input('email'))))),
            Limit::perMinute(120)->by('login-ip:'.$request->ip()),
        ]);
        RateLimiter::for('email-verification', fn (Request $request) => [
            Limit::perMinute(10)->by('verify-challenge:'.hash('sha256', (string) $request->input('challenge_token'))),
            Limit::perMinute(120)->by('verify-ip:'.$request->ip()),
        ]);
    }
}
