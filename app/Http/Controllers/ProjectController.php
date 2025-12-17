<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()->latest('updated_at')->get();

        return view('dashboard', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->projects()->create([
            'title' => $data['title'],
            'analysis_schema_version' => 'videocoach.analysis@1.0.0',
            'analysis_json' => [
                'schema' => [
                    'name' => 'videocoach.analysis',
                    'version' => '1.0.0',
                ],
                'drawings' => [],
                'keyframes' => [],
                'extensions' => [
                    'kvaPassthrough' => [],
                ],
            ],
        ]);

        return redirect()->route('dashboard')->with('status', 'プロジェクトを作成しました。');
    }

    public function editor(Project $project)
    {
        abort_unless(Auth::user()->is($project->user), 403);

        return view('editor', ['project' => $project]);
    }
}
