<x-layouts.app :title="__('Dashboard')">
    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-1">
                    <p class="text-sm font-semibold tracking-wide uppercase text-blue-600">
                        Video Coaching
                    </p>
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-semibold text-slate-900">プロジェクト一覧</h1>
                            <p class="text-sm text-slate-500">
                                アップロードした動画と注釈を一括管理します。
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <span>{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-blue-500 hover:text-blue-600"
                                    type="submit">
                                    ログアウト
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @if(session('status'))
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
                        {{ session('status') }}
                    </div>
                @endif
                <form id="new-project" method="POST" action="{{ route('projects.store') }}" class="space-y-3 sm:flex sm:items-end sm:space-y-0 sm:gap-3">
                    @csrf
                    <label class="flex-1 text-sm">
                        <span class="text-xs font-semibold uppercase text-slate-500">新規プロジェクト名</span>
                        <input
                            name="title"
                            required
                            maxlength="255"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-base text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="例: アナリシス 2025"
                        />
                    </label>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        新規プロジェクトを作成
                    </button>
                </form>
            </header>

            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">既存プロジェクト</h2>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Google風</p>
                </div>

                @if($projects->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        <p class="text-base font-semibold text-slate-900">まだプロジェクトがありません</p>
                        <p class="mt-2">アップロードした動画の注釈を残すには、まず新しいプロジェクトを作成しましょう。</p>
                        <p class="mt-4">
                            <a href="#new-project"
                               class="text-sm font-semibold text-blue-600 underline decoration-dotted underline-offset-4 hover:text-blue-800">
                                新規プロジェクトを作成する
                            </a>
                        </p>
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($projects as $project)
                            <article class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/50 p-5 transition hover:border-blue-400 hover:bg-white">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">プロジェクト</p>
                                        <h3 class="text-xl font-semibold text-slate-900">{{ $project->title }}</h3>
                                    </div>
                                    <span class="text-xs text-slate-500">
                                        更新 {{ $project->updated_at->format('n/j H:i') }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500">
                                    {{ $project->updated_at->diffForHumans() }}
                                </p>
                                <a
                                    href="{{ route('editor.show', $project) }}"
                                    class="mt-auto inline-flex items-center justify-center rounded-2xl border border-blue-600 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-50"
                                >
                                    開く（Editorへ）
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
