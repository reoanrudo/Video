<?php

use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->project = Project::create([
        'user_id' => $this->user->id,
        'title' => 'API Project',
        'analysis_schema_version' => 'videocoach.analysis@1.0.0',
        'analysis_json' => [
            'schema' => ['name' => 'videocoach.analysis', 'version' => '1.0.0'],
            'drawings' => [],
            'keyframes' => [],
            'extensions' => ['kvaPassthrough' => []],
        ],
    ]);
});

test('shows analysis for owner', function () {
    $this->actingAs($this->user);
    $response = $this->getJson(route('projects.analysis.show', $this->project));
    $response->assertOk()->assertJsonFragment(['schema_version' => 'videocoach.analysis@1.0.0']);
});

test('updates analysis for owner', function () {
    $this->actingAs($this->user);
    $payload = [
        'analysis' => [
            'schema' => ['name' => 'videocoach.analysis', 'version' => '1.0.0'],
            'drawings' => [
                ['id' => '1', 'type' => 'marker', 'geometry' => ['position' => ['x' => 10, 'y' => 10]]],
            ],
            'keyframes' => [],
            'extensions' => ['kvaPassthrough' => []],
        ],
    ];
    $response = $this->putJson(route('projects.analysis.update', $this->project), $payload);
    $response->assertOk();
    $this->assertDatabaseHas('projects', ['id' => $this->project->id, 'analysis_schema_version' => 'videocoach.analysis@1.0.0']);
});
