<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('Password!12345');

        $users = [
            ['name' => 'Test User Alpha', 'email' => 'test@example.com'],
            ['name' => 'Test User Bravo', 'email' => 'test+bravo@example.com'],
            ['name' => 'Test User Charlie', 'email' => 'test+charlie@example.com'],
            ['name' => 'Test User Delta', 'email' => 'test+delta@example.com'],
            ['name' => 'Test User Echo', 'email' => 'test+echo@example.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
