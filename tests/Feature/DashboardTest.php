<?php

use App\Models\Project;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard with projects', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'user_id' => $user->id,
        'title' => 'Existing Project',
        'analysis_schema_version' => 'videocoach.analysis@1.0.0',
        'analysis_json' => [
            'schema' => ['name' => 'videocoach.analysis', 'version' => '1.0.0'],
            'drawings' => [],
            'keyframes' => [],
            'extensions' => ['kvaPassthrough' => []],
        ],
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Existing Project');
    $response->assertSee(route('editor.show', $project));
});

test('dashboard can create projects and redirect to dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('projects.store'), [
        'title' => 'Newly Created',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('projects', [
        'title' => 'Newly Created',
        'user_id' => $user->id,
    ]);
});

test('editor placeholder is accessible for owner', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'user_id' => $user->id,
        'title' => 'Editor Test',
        'analysis_schema_version' => 'videocoach.analysis@1.0.0',
        'analysis_json' => [
            'schema' => ['name' => 'videocoach.analysis', 'version' => '1.0.0'],
            'drawings' => [],
            'keyframes' => [],
            'extensions' => ['kvaPassthrough' => []],
        ],
    ]);

    $this->actingAs($user);
    $response = $this->get(route('editor.show', $project));
    $response->assertStatus(200);
    $response->assertSee($project->title);
    $response->assertSee('Editor');
});
