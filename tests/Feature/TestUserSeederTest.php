<?php

use App\Models\User;
use Database\Seeders\TestUserSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

it('seeds test users with hashed passwords', function () {
    Artisan::call('db:seed', ['--class' => TestUserSeeder::class]);

    $emails = [
        'test@example.com',
        'test+bravo@example.com',
        'test+charlie@example.com',
        'test+delta@example.com',
        'test+echo@example.com',
    ];

    foreach ($emails as $email) {
        $user = User::where('email', $email)->first();
        expect($user)->not->toBeNull();
        expect(Hash::check('Password!12345', $user->password))->toBeTrue();
    }

    expect(User::count())->toBeGreaterThanOrEqual(count($emails));
});
