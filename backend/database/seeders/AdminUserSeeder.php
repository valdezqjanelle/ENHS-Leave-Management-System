<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; // single backslash
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // set a known password
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}