<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insertOrIgnore([
    [        
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // set a known password
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'email' => 'janellequinez@gmail.com',
            'password' => Hash::make('janelle123'), // set a known password
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
            ],

         [ 'email' => 'kathryningiaen@gmail.com',
                'password' => Hash::make('kathryn123'), // set a known password
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
         ]
        ]);

    }
}