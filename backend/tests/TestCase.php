<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test users are treated as email-verified unless a test says otherwise.
        User::creating(function (User $user) {
            $user->email_verified_at ??= now();
        });
    }
}