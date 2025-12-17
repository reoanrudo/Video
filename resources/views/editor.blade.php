<x-layouts.app :title="__('Editor')">
    <style>
        .editor-app { display: flex; flex-direction: column; height: 100vh; background: #f8fafc; }
        .editor-header { background: #ffffff; padding: 8px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
        .editor-logo { font-weight: 600; font-size: 15px; color: #3b82f6; display: flex; align-items: center; gap: 8px; }
        .editor-header-btns { display: flex; gap: 8px; }
        .editor-btn { padding: 6px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.15s; background: #fff; color: #475569; font-weight: 500; }
        .editor-btn:hover { border-color: #3b82f6; color: #3b82f6; }
        .editor-btn-primary { background: #3b82f6; color: #fff; border-color: #3b82f6; }
        .editor-btn-primary:hover { background: #2563eb; border-color: #2563eb; color: #fff; }
        .editor-main { display: flex; flex: 1; overflow: hidden; }
        .editor-toolbar { width: 56px; background: #ffffff; padding: 12px 8px; display: flex; flex-direction: column; gap: 4px; border-right: 1px solid #e2e8f0; }
        .editor-tool-btn { width: 40px; height: 40px; border: none; border-radius: 10px; background: transparent; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.15s; margin: 0 auto; }
        .editor-tool-btn:hover { background: #f1f5f9; color: #475569; }
        .editor-tool-btn.active { background: #3b82f6; color: #ffffff; box-shadow: 0 2px 8px rgba(59,130,246,0.3); }
        .editor-tool-divider { height: 1px; background: #e2e8f0; margin: 8px 4px; }
        .editor-color-input { width: 32px; height: 32px; border: 2px solid #e2e8f0; border-radius: 50%; cursor: pointer; margin: 4px auto; padding: 0; }
        .editor-video-area { flex: 1; padding: 16px; display: flex; justify-content: center; overflow: hidden; background: #f1f5f9; }
        .editor-video-panel { background: #ffffff; border-radius: 16px; max-width: 900px; width: 100%; display: flex; flex-direction: column; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .editor-video-header { padding: 10px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #64748b; display: flex; align-items: center; justify-content: space-between; }
        .editor-video-container { position: relative; background: #0f172a; width: 100%; aspect-ratio: 16/9; overflow: hidden; }
        .editor-video-el { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; display: block; z-index: 1; }
        .editor-draw-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: auto; touch-action: none; z-index: 2; }
        .editor-upload-area { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; cursor: pointer; border-radius: 0; transition: background 0.15s; z-index: 3; }
        .editor-upload-area:hover { background: #f1f5f9; }
        .editor-upload-icon { font-size: 48px; margin-bottom: 12px; }
        .editor-upload-text { color: #64748b; font-size: 14px; }
        .editor-controls { padding: 16px; border-top: 1px solid #e2e8f0; }
        .editor-video-meta { display: flex; align-items: center; gap: 8px; }
        .editor-time-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-family: ui-monospace, monospace; font-size: 13px; color: #475569; }
        .editor-play-row { display: flex; justify-content: center; gap: 12px; margin-bottom: 16px; }
        .editor-ctrl-btn { width: 40px; height: 40px; border: 1px solid #e2e8f0; border-radius: 50%; background: #ffffff; color: #475569; cursor: pointer; font-size: 16px; transition: all 0.15s; display: flex; align-items: center; justify-content: center; }
        .editor-ctrl-btn:hover { border-color: #3b82f6; color: #3b82f6; }
        .editor-ctrl-btn.play { width: 52px; height: 52px; background: #3b82f6; color: #ffffff; font-size: 20px; border: none; box-shadow: 0 4px 12px rgba(59,130,246,0.3); }
        .editor-ctrl-btn.play:hover { background: #2563eb; }
        .editor-seek-bar { width: 100%; height: 6px; border-radius: 3px; -webkit-appearance: none; background: #e2e8f0; margin-bottom: 16px; cursor: pointer; }
        .editor-seek-bar::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: #3b82f6; cursor: pointer; box-shadow: 0 2px 6px rgba(59,130,246,0.3); }
        .editor-extra-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .editor-speed-group { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b; }
        .editor-speed-slider { width: 80px; accent-color: #3b82f6; }
        .editor-timeline-markers { position: relative; height: 16px; margin-bottom: 4px; }
        .editor-timeline-marker { position: absolute; width: 10px; height: 10px; background: #ef4444; border-radius: 50%; transform: translateX(-50%); cursor: pointer; transition: all 0.15s; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
        .editor-timeline-marker:hover { transform: translateX(-50%) scale(1.2); }
        .editor-side-panel { width: 300px; background: #ffffff; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }
        .editor-panel-tabs { display: flex; border-bottom: 1px solid #e2e8f0; }
        .editor-panel-tab { flex: 1; padding: 12px; border: none; background: transparent; color: #64748b; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s; border-bottom: 2px solid transparent; }
        .editor-panel-tab:hover { color: #475569; }
        .editor-panel-tab.active { color: #3b82f6; border-bottom-color: #3b82f6; background: #f8fafc; }
        .editor-panel-content { flex: 1; overflow-y: auto; padding: 16px; }
        .editor-panel-title { font-size: 11px; color: #94a3b8; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .editor-keyframe-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 10px; overflow: hidden; cursor: pointer; transition: all 0.15s; }
        .editor-keyframe-item:hover { border-color: #3b82f6; }
        .editor-keyframe-item.active { border-color: #3b82f6; background: #eff6ff; }
        .editor-keyframe-header { display: flex; align-items: center; gap: 10px; padding: 10px; }
        .editor-keyframe-thumb { width: 72px; height: 40px; background: #e2e8f0; border-radius: 6px; object-fit: cover; flex-shrink: 0; }
        .editor-keyframe-info { flex: 1; min-width: 0; }
        .editor-keyframe-time { font-family: ui-monospace, monospace; font-size: 13px; color: #3b82f6; font-weight: 600; }
        .editor-keyframe-title { font-size: 12px; color: #475569; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .editor-keyframe-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .editor-keyframe-actions { display: flex; gap: 4px; }
        .editor-keyframe-actions button { width: 28px; height: 28px; border: none; border-radius: 6px; background: #e2e8f0; color: #64748b; cursor: pointer; font-size: 12px; transition: all 0.15s; }
        .editor-keyframe-actions button:hover { background: #ef4444; color: #ffffff; }
        .editor-anno-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 6px; font-size: 12px; }
        .editor-anno-color { width: 14px; height: 14px; border-radius: 4px; flex-shrink: 0; border: 1px solid rgba(0,0,0,0.1); }
        .editor-anno-name { flex: 1; color: #475569; }
        .editor-anno-del { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 16px; padding: 4px; }
        .editor-anno-del:hover { color: #dc2626; }
        .editor-empty { color: #94a3b8; font-size: 13px; text-align: center; padding: 24px 16px; }
        .editor-style-section { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 16px; border: 1px solid #e2e8f0; }
        .editor-style-label { font-size: 12px; color: #64748b; margin-bottom: 6px; display: block; }
        .editor-style-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .editor-style-row:last-child { margin-bottom: 0; }
        .editor-style-input { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 13px; width: 100px; }
        .editor-style-input:focus { outline: none; border-color: #3b82f6; }
        .editor-style-color { width: 40px; height: 32px; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; }
        .editor-style-range { width: 100px; accent-color: #3b82f6; }
        .editor-hidden { display: none !important; }
    </style>

    <div class="editor-app" id="editor-root"
         data-project-id="{{ $project->id }}"
         data-analysis='@json($project->analysis_json)'
         data-api-get="{{ route('projects.analysis.show', $project) }}"
         data-api-put="{{ route('projects.analysis.update', $project) }}">

        <header class="editor-header">
            <div class="editor-logo">
                <span>Video Coach Pro</span>
                <span style="color: #64748b; font-weight: 400; margin-left: 8px;">/ {{ $project->title }}</span>
            </div>
            <div class="editor-header-btns">
                <button class="editor-btn" id="undo-btn" title="Undo (Ctrl+Z)">Undo</button>
                <button class="editor-btn" id="redo-btn" title="Redo (Ctrl+Shift+Z)">Redo</button>
                <button class="editor-btn" id="clear-btn">描画消去</button>
                <button class="editor-btn editor-btn-primary" id="save-btn">保存</button>
                <a href="{{ route('dashboard') }}" class="editor-btn">戻る</a>
            </div>
        </header>

        <div class="editor-main">
            <div class="editor-toolbar">
                <button class="editor-tool-btn active" data-tool="select" title="選択・移動">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/></svg>
                </button>
                <div class="editor-tool-divider"></div>
                <button class="editor-tool-btn" data-tool="pen" title="ペン">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/></svg>
                </button>
                <button class="editor-tool-btn" data-tool="line" title="直線">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="19" x2="19" y2="5"/></svg>
                </button>
                <button class="editor-tool-btn" data-tool="arrow" title="矢印">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="19" x2="19" y2="5"/><polyline points="9 5 19 5 19 15"/></svg>
                </button>
                <button class="editor-tool-btn" data-tool="shape" title="円">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>
                </button>
                <div class="editor-tool-divider"></div>
                <button class="editor-tool-btn" data-tool="marker" title="クロスマーカー">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <button class="editor-tool-btn" data-tool="text" title="テキスト">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                </button>
                <div class="editor-tool-divider"></div>
                <button class="editor-tool-btn" data-tool="angle" title="角度計測">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </button>
                <div class="editor-tool-divider"></div>
                <input type="color" class="editor-color-input" id="style-color" value="#3b82f6" title="描画色">
            </div>

            <div class="editor-video-area">
                <div class="editor-video-panel">
                    <div class="editor-video-header">
                        <span id="video-name">動画を選択してください</span>
                        <div class="editor-video-meta">
                            <button class="editor-btn" id="debug-toggle" type="button" style="padding: 4px 10px; font-size: 12px;">Debug</button>
                            <span id="status-indicator" style="font-size: 11px; color: #94a3b8;">Idle</span>
                        </div>
                    </div>
                    <div class="editor-video-container" id="video-container" wire:ignore>
                        <video class="editor-video-el" id="editor-video" wire:ignore></video>
                        <canvas class="editor-draw-canvas" id="editor-canvas" wire:ignore></canvas>
                        <label class="editor-upload-area" id="upload-area">
                            <input type="file" accept="video/*" id="video-input" hidden>
                            <div class="editor-upload-icon">📁</div>
                            <div class="editor-upload-text">クリックして動画を選択</div>
                        </label>
                    </div>
                    <div class="editor-controls" id="video-controls" style="display:none;">
                        <div class="editor-time-row">
                            <span id="current-time">0:00.00</span>
                            <span id="duration">0:00.00</span>
                        </div>
                        <div class="editor-timeline-markers" id="timeline-markers"></div>
                        <input type="range" class="editor-seek-bar" id="seek-bar" min="0" max="100" value="0" step="0.001">
                        <div class="editor-play-row">
                            <button class="editor-ctrl-btn" id="prev-frame" title="前のフレーム">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
                            </button>
                            <button class="editor-ctrl-btn play" id="play-btn" title="再生/一時停止">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" id="play-icon"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </button>
                            <button class="editor-ctrl-btn" id="next-frame" title="次のフレーム">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
                            </button>
                        </div>
                        <div class="editor-extra-row">
                            <button class="editor-btn editor-btn-primary" id="keyframe-btn">キーフレーム追加</button>
                            <div class="editor-speed-group">
                                速度: <input type="range" class="editor-speed-slider" id="speed-slider" min="0.25" max="2" step="0.25" value="1">
                                <span id="speed-val">1x</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="editor-side-panel">
                <div class="editor-panel-tabs">
                    <button class="editor-panel-tab active" data-tab="keyframes">キーフレーム</button>
                    <button class="editor-panel-tab" data-tab="drawings">描画</button>
                    <button class="editor-panel-tab" data-tab="style">スタイル</button>
                </div>
                <div class="editor-panel-content">
                    <div id="keyframes-panel">
                        <div class="editor-panel-title">
                            <span>キーフレーム一覧</span>
                            <span id="keyframe-count">0件</span>
                        </div>
                        <div id="keyframe-list">
                            <div class="editor-empty">キーフレームがありません<br><small>動画再生中に「キーフレーム追加」で保存</small></div>
                        </div>
                    </div>
                    <div id="drawings-panel" class="editor-hidden">
                        <div class="editor-panel-title">
                            <span>現在の描画</span>
                            <span id="drawing-count">0件</span>
                        </div>
                        <div id="drawing-list">
                            <div class="editor-empty">描画がありません</div>
                        </div>
                    </div>
                    <div id="style-panel" class="editor-hidden">
                        <div class="editor-panel-title">
                            <span>スタイル設定</span>
                        </div>
                        <div class="editor-style-section">
                            <div class="editor-style-row">
                                <span class="editor-style-label">色</span>
                                <input type="color" class="editor-style-color" id="style-color-picker" value="#3b82f6">
                            </div>
                            <div class="editor-style-row">
                                <span class="editor-style-label">線幅</span>
                                <input type="range" class="editor-style-range" id="style-width" min="1" max="12" value="2">
                                <span id="style-width-value" style="width: 24px; text-align: right; font-size: 12px; color: #64748b;">2</span>
                            </div>
                            <div class="editor-style-row">
                                <span class="editor-style-label">不透明度</span>
                                <input type="range" class="editor-style-range" id="style-opacity" min="0.1" max="1" step="0.05" value="1">
                                <span id="style-opacity-value" style="width: 32px; text-align: right; font-size: 12px; color: #64748b;">1.00</span>
                            </div>
                        </div>
                        <div class="editor-style-section">
                            <div class="editor-style-row">
                                <span class="editor-style-label">選択中</span>
                                <span id="selection-label" style="font-size: 13px; color: #475569;">なし</span>
                            </div>
                            <button class="editor-btn" id="delete-btn" style="width: 100%; justify-content: center; margin-top: 8px;">選択を削除</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
