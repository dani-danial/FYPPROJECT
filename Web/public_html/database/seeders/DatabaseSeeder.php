<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
   public function run()
{
    \App\Models\User::create([
        'name' => 'Admin User',
        'username' => 'admin',
        'email' => 'admin@runtracker.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'status' => 'active', //
    ]);
}
}
