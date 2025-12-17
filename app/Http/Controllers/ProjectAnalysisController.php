<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAnalysisRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProjectAnalysisController extends Controller
{
    public function show(Project $project): JsonResponse
    {
        abort_unless(Auth::user()->is($project->user), 403);

        return response()->json([
            'analysis' => $project->analysis_json,
            'schema_version' => $project->analysis_schema_version,
        ]);
    }

    public function update(SaveAnalysisRequest $request, Project $project): JsonResponse
    {
        abort_unless(Auth::user()->is($project->user), 403);

        $analysis = $request->validated('analysis');

        $project->update([
            'analysis_schema_version' => $analysis['schema']['name'].'@'.$analysis['schema']['version'],
            'analysis_json' => $analysis,
        ]);

        return response()->json(['status' => 'ok']);
    }
}
