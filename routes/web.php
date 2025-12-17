<?php

use App\Http\Controllers\ProjectAnalysisController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// コーチングノート（シングルページ版）
Route::get('/coach', function () {
    return view('coach');
});

Route::get('dashboard', [ProjectController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::post('projects', [ProjectController::class, 'store'])
        ->name('projects.store');

    Route::get('editor/{project}', [ProjectController::class, 'editor'])
        ->name('editor.show');

    Route::get('api/projects/{project}/analysis', [ProjectAnalysisController::class, 'show'])
        ->name('projects.analysis.show');
    Route::put('api/projects/{project}/analysis', [ProjectAnalysisController::class, 'update'])
        ->name('projects.analysis.update');
});
