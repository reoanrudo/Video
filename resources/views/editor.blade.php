<x-layouts.plain :title="__('動画コーチングノート')">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Google Sans','Product Sans','Roboto','Noto Sans JP',sans-serif; background: #0b0f16; min-height: 100vh; }

        /* Kinovea風フレーム */
        .container {
            height: 100vh;
            display: grid;
            grid-template-rows: 32px 36px 1fr 140px 52px;
            background: #0b0f16;
            overflow: hidden;
        }

        .header {
            height: 32px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 10px;
            background: #e9edf3;
            border-bottom: 1px solid rgba(0,0,0,0.18);
            color: #111827;
            user-select: none;
        }

        .menu-item {
            font-size: 13px;
            padding: 4px 6px;
            border-radius: 3px;
            cursor: default;
        }

        .menu-item:hover { background: rgba(0,0,0,0.06); }

        .topbar {
            height: 36px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 10px;
            background: #f4f6fa;
            border-bottom: 1px solid rgba(0,0,0,0.18);
        }

        .topbar .title {
            font-size: 13px;
            color: #334155;
            margin-right: 8px;
        }

        .topbar .tool-mini {
            height: 28px;
            min-width: 28px;
            padding: 0 8px;
            border: 1px solid rgba(15,23,42,0.12);
            border-radius: 4px;
            background: #ffffff;
            color: #0f172a;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
        }

        .topbar .tool-mini:hover { background: #f8fafc; }
        .topbar .spacer { flex: 1; }

        .main-content {
            position: relative;
            background: #000;
            overflow: hidden;
        }

        /* 中央ビューア */
        .viewer {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 10px;
        }

        .viewer-inner {
            position: relative;
            width: min(100%, 1400px);
            height: min(100%, 820px);
            display: grid;
            place-items: center;
        }

        .toolbar {
            position: absolute;
            left: 10px;
            bottom: 10px;
            display: flex;
            gap: 6px;
            align-items: center;
            padding: 6px;
            border-radius: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            overflow-x: auto;
            max-width: calc(100% - 20px);
        }

        .tool-btn { width: 56px; height: 56px; border: none; background: transparent; border-radius: 50%; cursor: pointer; font-size: 24px; display: flex; align-items: center; justify-content: center; transition: background 0.2s; position: relative; }
        .tool-btn:hover { background: rgba(255,255,255,0.14); }
        .tool-btn:active { background: rgba(255,255,255,0.18); }
        .tool-btn.active { background: #e8f0fe; color: #1967d2; }
        .tool-btn.active::after { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 32px; background: #1967d2; border-radius: 0 2px 2px 0; }
        .color-palette { display: flex; flex-direction: row; gap: 8px; margin-left: 8px; padding-left: 8px; border-left: 1px solid rgba(255,255,255,0.18); }
        .color-btn { width: 32px; height: 32px; border: 2px solid transparent; border-radius: 50%; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); }
        .color-btn:hover { transform: scale(1.15); box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15); }
        .color-btn.active { border: 3px solid #1967d2; box-shadow: 0 0 0 1px white, 0 0 0 3px #1967d2; }
        .video-area { padding: 0; display: block; background: transparent; }
        .upload-zone { border: 2px dashed rgba(255,255,255,0.35); border-radius: 10px; padding: 60px 40px; text-align: center; background: rgba(255,255,255,0.06); cursor: pointer; transition: all 0.2s; color: rgba(255,255,255,0.92); }
        .upload-zone:hover { background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.55); }
        .upload-zone.dragover { background: rgba(25,103,210,0.15); border-color: rgba(25,103,210,0.8); border-width: 3px; }
        .upload-icon { font-size: 44px; margin-bottom: 12px; opacity: 0.9; }
        .upload-zone h2 { font-size: 18px; font-weight: 500; color: rgba(255,255,255,0.92); margin-bottom: 6px; }
        .upload-zone p { font-size: 13px; color: rgba(255,255,255,0.70); }

        .video-container { position: relative; background: #000; border-radius: 8px; overflow: hidden; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.35); }
        .video-container.active { display: block; }
        .canvas-wrapper { position: relative; }
        #videoPlayer { width: 100%; display: block; }
        #drawingCanvas { position: absolute; top: 0; left: 0; cursor: crosshair; }
        /* 下部フィルムストリップ */
        .filmstrip {
            background: #3b3f46;
            border-top: 1px solid rgba(255,255,255,0.08);
            overflow: hidden;
            padding: 8px 10px;
        }

        .filmstrip .memo-list {
            display: flex;
            flex-direction: row;
            gap: 10px;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 6px;
        }

        .filmstrip .memo-item {
            width: 150px;
            border-radius: 6px;
            border-left: none;
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: none;
        }

        .filmstrip .memo-text,
        .filmstrip .memo-actions,
        .filmstrip .tag-list,
        .filmstrip .empty-state p:nth-child(2),
        .filmstrip .empty-state p:nth-child(3) { display: none; }

        .filmstrip .memo-timestamp { background: rgba(37,99,235,0.8); }

        .filmstrip .memo-thumbnail { height: 90px; margin-top: 8px; }

        /* タイムライン（Kinoveaっぽく下にまとめる） */
        .timeline {
            background: #e9edf3;
            border-top: 1px solid rgba(0,0,0,0.18);
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-bar { flex: 1; height: 8px; background: rgba(15,23,42,0.18); border-radius: 4px; cursor: pointer; position: relative; }
        .progress-fill { height: 100%; background: #22c55e; border-radius: 4px; width: 0%; transition: width 0.1s; }

        .controls {
            background: #f4f6fa;
            border-top: 1px solid rgba(0,0,0,0.18);
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .play-btn { background: #ffffff; border: 1px solid rgba(15,23,42,0.12); color: #0f172a; width: 36px; height: 36px; border-radius: 6px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; }
        .play-btn:hover { background: #f8fafc; }
        .time-display { font-size: 12px; min-width: 120px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; color: #0f172a; }
        .volume-control { display: flex; align-items: center; gap: 6px; }
        .volume-slider { width: 90px; height: 4px; -webkit-appearance: none; background: rgba(15,23,42,0.18); border-radius: 999px; outline: none; }
        .volume-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; background: #0f172a; border-radius: 50%; cursor: pointer; }
        .speed-control { background: #ffffff; border: 1px solid rgba(15,23,42,0.12); color: #0f172a; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .speed-control:hover { background: #f8fafc; }
        .capture-btn { background: #2563eb; border: none; color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; }
        .capture-btn:hover { background: #1d4ed8; }

        /* メモドロワ */
        .side-panel {
            position: absolute;
            top: 68px; /* header + topbar */
            right: 0;
            height: calc(100% - 68px);
            width: 380px;
            background: #ffffff;
            border-left: 1px solid rgba(0,0,0,0.16);
            box-shadow: -10px 0 30px rgba(0,0,0,0.25);
            transform: translateX(100%);
            transition: transform 0.2s ease;
            z-index: 50;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .side-panel.open { transform: translateX(0); }
        .panel-tabs { display: flex; background: white; border-bottom: 1px solid #dadce0; padding: 0 4px; }
        .tab-btn { flex: 1; padding: 14px 16px; border: none; background: none; cursor: pointer; font-size: 14px; font-weight: 500; color: #5f6368; border-bottom: 2px solid transparent; transition: all 0.2s; position: relative; }
        .tab-btn:hover { background: rgba(60,64,67,0.04); }
        .tab-btn.active { color: #1967d2; border-bottom-color: #1967d2; }
        .panel-content { flex: 1; overflow-y: auto; padding: 16px; background: #f8f9fa; }
        .memo-form { background: white; padding: 16px; border-radius: 8px; margin-bottom: 16px; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); }
        .memo-textarea { width: 100%; min-height: 100px; border: 1px solid #dadce0; border-radius: 4px; padding: 12px; font-size: 14px; resize: vertical; font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s; }
        .memo-textarea:focus { outline: none; border-color: #1967d2; box-shadow: 0 0 0 1px #1967d2; }
        .tag-section { margin-top: 16px; }
        .tag-section label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 500; color: #5f6368; text-transform: uppercase; letter-spacing: 0.3px; }
        .tag-manager { background: #f8f9fa; padding: 12px; border-radius: 4px; margin-bottom: 12px; border: 1px solid #dadce0; }
        .predefined-tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .predefined-tag { background: white; border: 1px solid #dadce0; padding: 6px 12px; border-radius: 16px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s; color: #5f6368; }
        .predefined-tag:hover { background: #f8f9fa; border-color: #1967d2; }
        .predefined-tag.selected { background: #e8f0fe; color: #1967d2; border-color: #1967d2; }
        .tag-input-wrapper { display: flex; gap: 8px; }
        .tag-input { flex: 1; border: 1px solid #dadce0; border-radius: 4px; padding: 8px 12px; font-size: 14px; transition: border-color 0.2s, box-shadow 0.2s; }
        .tag-input:focus { outline: none; border-color: #1967d2; box-shadow: 0 0 0 1px #1967d2; }
        .tag-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .tag { background: #e8f0fe; color: #1967d2; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .tag-remove { background: none; border: none; color: #1967d2; cursor: pointer; font-size: 16px; padding: 0; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: background 0.2s; }
        .tag-remove:hover { background: rgba(25,103,210,0.1); }
        .memo-list { display: flex; flex-direction: column; gap: 12px; }
        .memo-item { background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); cursor: pointer; transition: all 0.2s; border-left: 3px solid #1967d2; }
        .memo-item:hover { box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15); }
        .memo-timestamp { background: #1967d2; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 500; font-family: 'Roboto Mono', monospace; }
        .memo-actions { display: flex; gap: 8px; margin-top: 12px; }
        .memo-action-btn { background: #f8f9fa; border: 1px solid #dadce0; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; color: #5f6368; transition: all 0.2s; font-weight: 500; }
        .memo-action-btn:hover { background: #f1f3f4; border-color: #5f6368; }
        .share-section { padding: 16px; }
        .share-btn { width: 100%; background: #1967d2; color: white; border: none; padding: 12px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); }
        .share-btn:hover { background: #1557b0; box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15); }
        .share-btn:active { background: #0d47a1; }
        .share-info { background: #fef7e0; color: #5f6368; padding: 16px; border-radius: 4px; border-left: 3px solid #f9ab00; margin-top: 16px; font-size: 13px; line-height: 1.6; }
        .empty-state { text-align: center; padding: 48px 24px; color: #5f6368; }
        .empty-state-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }
        @media (max-width: 900px) {
            .side-panel { width: min(92vw, 380px); }
            .toolbar .tool-btn { width: 46px; height: 46px; font-size: 18px; }
        }
        ::-webkit-scrollbar { width: 12px; height: 12px; }
        ::-webkit-scrollbar-thumb { background: #dadce0; border-radius: 6px; border: 3px solid transparent; background-clip: content-box; }
        ::-webkit-scrollbar-thumb:hover { background: #bdc1c6; background-clip: content-box; }
    </style>

    <div class="container">
        <div class="header" aria-label="Kinovea Menu Bar">
            <div class="menu-item">ファイル</div>
            <div class="menu-item">編集</div>
            <div class="menu-item">ビュー</div>
            <div class="menu-item">画像</div>
            <div class="menu-item">ビデオ</div>
            <div class="menu-item">ツール</div>
            <div class="menu-item">ダイアログ</div>
            <div class="menu-item">オプション</div>
            <div class="menu-item">ヘルプ</div>
        </div>

        <div class="topbar">
            <span class="title">Kinovea風レイアウト（Web版）</span>
            <button class="tool-mini" id="toggleNotesBtn" type="button">📝 メモ</button>
            <div class="spacer"></div>
            <button class="tool-mini" id="openVideoBtn" type="button">📁 動画</button>
        </div>

        <div class="main-content">
            <div class="viewer">
                <div class="viewer-inner">
                    <div class="video-area">
                        <div class="upload-zone" id="uploadZone">
                            <div class="upload-icon">📁</div>
                            <h2>動画をアップロード</h2>
                            <p>ドラッグ&ドロップまたはクリック</p>
                            <input type="file" id="videoInput" accept="video/*" style="display:none;">
                        </div>

                        <div class="video-container" id="videoContainer">
                            <div class="canvas-wrapper">
                                <video id="videoPlayer"></video>
                                <canvas id="drawingCanvas"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="toolbar" id="toolbar">
                <button class="tool-btn active" data-tool="select" title="選択"><span>↖️</span></button>
                <button class="tool-btn" data-tool="pen" title="ペン"><span>✏️</span></button>
                <button class="tool-btn" data-tool="move" title="移動(パン)"><span>🖐️</span></button>
                <button class="tool-btn" data-tool="bezier" title="ベジェ曲線（パス）"><span>🖊️</span></button>
                <button class="tool-btn" data-tool="line" title="直線"><span>📏</span></button>
                <button class="tool-btn" data-tool="curve" title="曲線"><span>〰️</span></button>
                <button class="tool-btn" data-tool="circle" title="円"><span>⭕</span></button>
                <button class="tool-btn" data-tool="marker" title="クロスマーカー"><span>✚</span></button>
                <button class="tool-btn" data-tool="rectangle" title="長方形"><span>▭</span></button>
                <button class="tool-btn" data-tool="polyline" title="ポリライン"><span>〰️➕</span></button>
                <button class="tool-btn" data-tool="angle" title="角度計測"><span>∠</span></button>
                <button class="tool-btn" data-tool="arrow" title="矢印"><span>➡️</span></button>
                <button class="tool-btn" data-tool="curved-arrow" title="曲線矢印"><span>↪️</span></button>
                <button class="tool-btn" id="flipCurveBtn" title="曲線の向きを反転"><span>🔄</span></button>
                <button class="tool-btn" data-tool="text" title="テキスト"><span>T</span></button>
                <button class="tool-btn" data-tool="eraser" title="消しゴム"><span>🧽</span></button>
                <button class="tool-btn" id="undoBtn" title="元に戻す"><span>↶</span></button>
                <button class="tool-btn" id="redoBtn" title="やり直す"><span>↷</span></button>
                <button class="tool-btn" id="clearBtn" title="すべてクリア"><span>🗑️</span></button>
                <div class="color-palette" id="colorPalette" aria-label="Color Palette">
                    <div class="color-btn active" data-color="#EA4335" style="background:#EA4335;" title="赤"></div>
                    <div class="color-btn" data-color="#34A853" style="background:#34A853;" title="緑"></div>
                    <div class="color-btn" data-color="#4285F4" style="background:#4285F4;" title="青"></div>
                    <div class="color-btn" data-color="#FBBC04" style="background:#FBBC04;" title="黄"></div>
                    <div class="color-btn" data-color="#FFFFFF" style="background:#FFFFFF; border:1px solid rgba(255,255,255,0.35);" title="白"></div>
                    <div class="color-btn" data-color="#202124" style="background:#202124;" title="黒"></div>
                </div>
            </div>
                </div>
            </div>

            <!-- メモドロワ（右） -->
            <div class="side-panel" id="notesDrawer" aria-label="Notes Drawer">
                <div class="panel-tabs">
                    <button class="tab-btn active" data-tab="memos" type="button">メモ</button>
                    <button class="tab-btn" data-tab="share" type="button">共有</button>
                </div>
                <div class="panel-content">
                    <div id="memosTab" class="tab-content">
                        <div class="memo-form">
                            <textarea class="memo-textarea" id="memoText" placeholder="メモを入力..."></textarea>
                            <div class="tag-section">
                                <label>タグ</label>
                                <div class="tag-manager">
                                    <div class="predefined-tags" id="predefinedTags">
                                        <div class="predefined-tag" data-tag="フォーム">#フォーム</div>
                                        <div class="predefined-tag" data-tag="改善点">#改善点</div>
                                        <div class="predefined-tag" data-tag="良い点">#良い点</div>
                                        <div class="predefined-tag" data-tag="重要">#重要</div>
                                    </div>
                                </div>
                                <div class="tag-input-wrapper">
                                    <input type="text" class="tag-input" id="tagInput" placeholder="カスタムタグ...">
                                </div>
                                <div class="tag-list" id="selectedTags"></div>
                            </div>
                            <button class="save-memo-btn" id="saveMemoBtn" type="button"><span>💾</span> メモを保存</button>
                        </div>
                    </div>
                    <div id="shareTab" class="tab-content" style="display:none;">
                        <div class="share-section">
                            <button class="share-btn" id="shareBtn" type="button"><span>🔗</span> リンクを生成</button>
                            <div class="share-info">
                                <strong>※ 現在はデモ版のため共有は未実装です</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="filmstrip" aria-label="Filmstrip">
            <div class="memo-list" id="memoList">
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>まだメモがありません</p>
                </div>
            </div>
        </div>

        <div class="timeline" aria-label="Timeline">
            <div class="time-display" id="timeDisplay">0:00 / 0:00</div>
            <div class="progress-bar" id="progressBar"><div class="progress-fill" id="progressFill"></div></div>
        </div>

        <div class="controls" aria-label="Transport Controls">
            <button class="play-btn" id="playBtn" type="button">▶️</button>
            <div class="volume-control">
                <span>🔊</span>
                <input type="range" class="volume-slider" id="volumeSlider" min="0" max="100" value="100">
            </div>
            <button class="speed-control" id="speedControl" type="button">再生速度: 1x</button>
            <button class="capture-btn" id="captureBtn" type="button"><span>📸</span> キャプチャ</button>
            <button class="tool-mini" id="fullscreenBtn" type="button">⛶</button>
        </div>
    </div>

    <script>
    /**
     * ベクター描画エンジン（簡易版）
     * - すべての図形をオブジェクトとして保持し、毎回再描画
     * - 選択 & 移動 (ハンドル/図形単位)
     * - ラインの水平距離表示、角度計測、ポリライン、クロスマーカー対応
     * - ビットマップ保存は最後にレンダリング済みキャンバスを captureFrame() で取得
     */

    /********************** 共通 DOM **********************/
    const videoPlayer    = document.getElementById('videoPlayer');
    const drawingCanvas  = document.getElementById('drawingCanvas');
    const ctx            = drawingCanvas.getContext('2d');
    const videoInput     = document.getElementById('videoInput');
    const uploadZone     = document.getElementById('uploadZone');
    const videoContainer = document.getElementById('videoContainer');
    const playBtn        = document.getElementById('playBtn');
    const progressBar    = document.getElementById('progressBar');
    const progressFill   = document.getElementById('progressFill');
    const timeDisplay    = document.getElementById('timeDisplay');
    const volumeSlider   = document.getElementById('volumeSlider');
    const speedControl   = document.getElementById('speedControl');
    const captureBtn     = document.getElementById('captureBtn');
    const fullscreenBtn  = document.getElementById('fullscreenBtn');
    const memoText       = document.getElementById('memoText');
    const tagInput       = document.getElementById('tagInput');
    const selectedTags   = document.getElementById('selectedTags');
    const saveMemoBtn    = document.getElementById('saveMemoBtn');
    const memoList       = document.getElementById('memoList');
    const shareBtn       = document.getElementById('shareBtn');

    /********************** 状態管理 **********************/
    const state = {
        tool: 'select',
        color: '#EA4335',
        stroke: 3,
        shapes: [],
        selectedId: null,
        hoverHandle: null, // {shapeId, idx}
        draft: null,       // 描画中の暫定図形
        pan: { x: 0, y: 0 },
        isPanning: false,
        drag: null,        // {type:'shape'|'handle', shapeId, idx, start:{x,y}}
        history: [],
        redo: [],
        speedIndex: 2,
        polyWorking: [],   // ポリライン/フリーハンドの仮ポイント
        angleStep: 0,
        anglePoints: [],   // 3点
        curveDir: 1
    };

    const speeds = [0.25, 0.5, 1, 1.5, 2];

    /********************** 初期設定 **********************/
    // 初期キャンバスサイズ（動画未ロード時の安全値）
    drawingCanvas.width  = 1280;
    drawingCanvas.height = 720;

    uploadZone.addEventListener('click', () => videoInput.click());
    uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault(); uploadZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('video/')) loadVideo(file);
    });
    videoInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) loadVideo(file);
    });

    function loadVideo(file) {
        const url = URL.createObjectURL(file);
        videoPlayer.src = url;
        videoContainer.classList.add('active');
        uploadZone.style.display = 'none';
        videoPlayer.addEventListener('loadedmetadata', () => {
            drawingCanvas.width  = videoPlayer.videoWidth;
            drawingCanvas.height = videoPlayer.videoHeight;
            resizeCanvas();
            pushHistory();
            render();
        }, { once: true });
    }

    function resizeCanvas() {
        const rect = videoPlayer.getBoundingClientRect();
        drawingCanvas.style.width  = `${rect.width}px`;
        drawingCanvas.style.height = `${rect.height}px`;
        render();
    }
    window.addEventListener('resize', resizeCanvas);

    /********************** ヒストリ **********************/
    function cloneShapes(arr = state.shapes) {
        return arr.map(s => JSON.parse(JSON.stringify(s)));
    }
    function pushHistory() {
        state.history.push(cloneShapes());
        if (state.history.length > 50) state.history.shift();
        state.redo = [];
    }
    function undo() {
        if (state.history.length <= 1) return;
        const cur = state.history.pop();
        state.redo.push(cur);
        state.shapes = cloneShapes(state.history[state.history.length - 1]);
        state.selectedId = null;
        render();
    }
    function redo() {
        if (state.redo.length === 0) return;
        const next = state.redo.pop();
        state.history.push(cloneShapes(next));
        state.shapes = cloneShapes(next);
        render();
    }

    /********************** ツール/カラー UI **********************/
    // 上部ボタン
    const toggleNotesBtn = document.getElementById('toggleNotesBtn');
    const openVideoBtn = document.getElementById('openVideoBtn');
    const notesDrawer = document.getElementById('notesDrawer');

    openVideoBtn?.addEventListener('click', () => videoInput.click());
    toggleNotesBtn?.addEventListener('click', () => {
        notesDrawer?.classList.toggle('open');
    });

    document.querySelectorAll('.tool-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (['undoBtn','redoBtn','clearBtn','flipCurveBtn'].includes(btn.id)) return;
            document.querySelectorAll('.tool-btn').forEach(b => { if (!b.id) b.classList.remove('active'); });
            btn.classList.add('active');
            state.tool = btn.dataset.tool;
            state.draft = null;
            state.polyWorking = [];
            state.angleStep = 0;
            state.anglePoints = [];
            render();
        });
    });
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.color = btn.dataset.color;
        });
    });
    document.getElementById('undoBtn').addEventListener('click', undo);
    document.getElementById('redoBtn').addEventListener('click', redo);
    document.getElementById('flipCurveBtn').addEventListener('click', () => {
        state.curveDir *= -1;
        const btn = document.getElementById('flipCurveBtn');
        btn.style.background = state.curveDir === 1 ? 'transparent' : 'rgba(255,255,255,0.16)';
        btn.title = state.curveDir === 1 ? '曲線の向きを反転' : '曲線の向き: 反転中';
    });
    document.getElementById('clearBtn').addEventListener('click', () => {
        if (!confirm('描画をすべてクリアしますか？')) return;
        state.shapes = [];
        pushHistory();
        render();
    });

    /********************** ポインタイベント **********************/
    drawingCanvas.addEventListener('mousedown', pointerDown);
    drawingCanvas.addEventListener('mousemove', pointerMove);
    drawingCanvas.addEventListener('mouseup', pointerUp);
    drawingCanvas.addEventListener('mouseleave', pointerUp);
    drawingCanvas.addEventListener('dblclick', pointerDouble);

    function toWorld(e) {
        const rect = drawingCanvas.getBoundingClientRect();
        const sx = drawingCanvas.width / rect.width;
        const sy = drawingCanvas.height / rect.height;
        return {
            x: (e.clientX - rect.left) * sx - state.pan.x,
            y: (e.clientY - rect.top) * sy - state.pan.y
        };
    }

    function pointerDown(e) {
        const p = toWorld(e);

        // 消しゴム（ベクター版：当たった図形を削除）
        if (state.tool === 'eraser') {
            const hit = hitShape(p);
            if (!hit) return;
            state.shapes = state.shapes.filter(s => s.id !== hit.id);
            state.selectedId = null;
            pushHistory();
            render();
            return;
        }

        // 移動ツール: 図形があればそれを移動 / なければパン
        if (state.tool === 'move') {
            const handle = hitHandle(p);
            if (handle) {
                state.drag = { type: 'handle', shapeId: handle.shapeId, idx: handle.idx, start: p };
                state.selectedId = handle.shapeId;
                render();
                return;
            }
            const hit = hitShape(p);
            if (hit) {
                state.selectedId = hit.id;
                state.drag = { type: 'shape', shapeId: hit.id, start: p };
                render();
                return;
            }
            state.isPanning = true;
            state.drag = { type: 'pan', start: p };
            return;
        }

        // 選択モード: ハンドル優先でヒットテスト（図形を1個ずつ移動）
        if (state.tool === 'select') {
            const handle = hitHandle(p);
            if (handle) {
                state.drag = { type: 'handle', shapeId: handle.shapeId, idx: handle.idx, start: p };
                state.selectedId = handle.shapeId;
                return;
            }
            const hit = hitShape(p);
            if (hit) {
                state.selectedId = hit.id;
                state.drag = { type: 'shape', shapeId: hit.id, start: p };
            } else {
                state.selectedId = null;
            }
            render();
            return;
        }

        // テキスト（簡易）
        if (state.tool === 'text') {
            const text = window.prompt('テキストを入力', 'Note');
            if (!text) return;
            addShape({ type: 'text', points: [p], text, color: state.color, stroke: state.stroke });
            pushHistory();
            render();
            return;
        }

        // ポリライン（複数クリックで確定）
        if (state.tool === 'polyline') {
            if (state.polyWorking.length === 0) {
                state.polyWorking.push(p);
            } else {
                state.polyWorking.push(p);
            }
            render();
            return;
        }

        // 角度ツール（3クリック）
        if (state.tool === 'angle') {
            state.anglePoints.push(p);
            state.angleStep++;
            if (state.angleStep === 3) {
                addShape({ type: 'angle', points: [...state.anglePoints], color: state.color, stroke: state.stroke });
                state.angleStep = 0; state.anglePoints = [];
            }
            render();
            return;
        }

        // マーカーはワンクリック
        if (state.tool === 'marker') {
            addShape({ type: 'marker', points: [p], color: state.color, stroke: state.stroke });
            render();
            return;
        }

        // フリーハンド（ペン）
        if (state.tool === 'pen' || state.tool === 'bezier') {
            state.draft = { type: 'path', points: [p], color: state.color, stroke: state.stroke };
            return;
        }

        // 単発図形（line / rectangle / circle / arrow / curve / curved-arrow）
        if (state.tool === 'curve' || state.tool === 'curved-arrow') {
            state.draft = { type: state.tool, points: [p, p], curveDir: state.curveDir, color: state.color, stroke: state.stroke };
            return;
        }
        state.draft = { type: state.tool, points: [p, p], color: state.color, stroke: state.stroke };
    }

    function pointerMove(e) {
        const p = toWorld(e);

        if (state.isPanning && state.drag?.type === 'pan') {
            const dx = p.x - state.drag.start.x;
            const dy = p.y - state.drag.start.y;
            state.pan.x += dx; state.pan.y += dy;
            state.drag.start = p;
            render();
            return;
        }

        if (state.drag && state.drag.type === 'shape') {
            const shape = state.shapes.find(s => s.id === state.drag.shapeId);
            if (!shape) return;
            const dx = p.x - state.drag.start.x;
            const dy = p.y - state.drag.start.y;
            moveShape(shape, dx, dy);
            state.drag.start = p;
            if (dx !== 0 || dy !== 0) state.drag.moved = true;
            render();
            return;
        }

        if (state.drag && state.drag.type === 'handle') {
            const shape = state.shapes.find(s => s.id === state.drag.shapeId);
            if (!shape) return;
            shape.points[state.drag.idx] = p;
            state.drag.moved = true;
            render();
            return;
        }

        if (state.tool === 'select') {
            // ハンドルホバー表示のみ
            state.hoverHandle = hitHandle(p);
            render();
            return;
        }

        if (state.draft) {
            if (state.draft.type === 'path') {
                state.draft.points.push(p);
            } else {
                state.draft.points[1] = p;
            }
            render();
            return;
        }

        // ポリラインの仮プレビュー
        if (state.tool === 'polyline' && state.polyWorking.length > 0) {
            render(p);
        }
    }

    function pointerUp(e) {
        if (state.isPanning) { state.isPanning = false; state.drag = null; return; }

        if (state.drag && ['shape','handle'].includes(state.drag.type)) {
            if (state.drag.moved) pushHistory();
            state.drag = null;
            return;
        }

        if (!state.draft) return;

        // クリックで終了する単発図形
        if (state.draft.type === 'path') {
            addShape(state.draft);
        } else {
            addShape(state.draft);
        }
        state.draft = null;
        pushHistory();
        render();
    }

    function pointerDouble(e) {
        if (state.tool === 'polyline' && state.polyWorking.length >= 2) {
            addShape({ type: 'polyline', points: [...state.polyWorking], color: state.color, stroke: state.stroke });
            state.polyWorking = [];
            pushHistory();
            render();
        }
    }

    /********************** 形状操作 **********************/
    let shapeSeq = 1;
    function addShape(shape) {
        shape.id = shapeSeq++;
        state.shapes.push(JSON.parse(JSON.stringify(shape)));
    }

    function moveShape(shape, dx, dy) {
        shape.points = shape.points.map(pt => ({ x: pt.x + dx, y: pt.y + dy }));
    }

    function hitShape(p) {
        // 上にあるもの優先
        for (let i = state.shapes.length - 1; i >= 0; i--) {
            const s = state.shapes[i];
            if (isHit(s, p)) return s;
        }
        return null;
    }

    function getHandles(shape) {
        const pts = shape.points || [];
        if (pts.length === 0) return [];
        switch (shape.type) {
            case 'line':
            case 'arrow':
            case 'rectangle':
            case 'circle':
                return [
                    { x: pts[0].x, y: pts[0].y, idx: 0 },
                    { x: pts[1].x, y: pts[1].y, idx: 1 },
                ];
            case 'marker':
                return [{ x: pts[0].x, y: pts[0].y, idx: 0 }];
            case 'polyline':
            case 'path': {
                if (pts.length === 1) return [{ x: pts[0].x, y: pts[0].y, idx: 0 }];
                return [
                    { x: pts[0].x, y: pts[0].y, idx: 0 },
                    { x: pts[pts.length - 1].x, y: pts[pts.length - 1].y, idx: pts.length - 1 },
                ];
            }
            case 'angle':
                return pts.slice(0, 3).map((p, idx) => ({ x: p.x, y: p.y, idx }));
            default:
                return [{ x: pts[0].x, y: pts[0].y, idx: 0 }];
        }
    }

    function hitHandle(p) {
        const radius = 10;
        for (let i = state.shapes.length - 1; i >= 0; i--) {
            const s = state.shapes[i];
            const handles = getHandles(s);
            for (const h of handles) {
                if (dist(h, p) <= radius) return { shapeId: s.id, idx: h.idx };
            }
        }
        return null;
    }

    function isHit(shape, p) {
        const tol = 8;
        const pts = shape.points;
        switch (shape.type) {
            case 'line':
            case 'arrow':
                return pointToSegmentDist(p, pts[0], pts[1]) <= tol;
            case 'curve':
            case 'curved-arrow':
                // 近似的に直線として判定
                return pointToSegmentDist(p, pts[0], pts[1]) <= tol;
            case 'rectangle': {
                const [a, b] = pts;
                return p.x >= Math.min(a.x, b.x) - tol && p.x <= Math.max(a.x, b.x) + tol &&
                       p.y >= Math.min(a.y, b.y) - tol && p.y <= Math.max(a.y, b.y) + tol;
            }
            case 'circle': {
                const r = dist(pts[0], pts[1]);
                const d = dist(pts[0], p);
                return Math.abs(d - r) <= tol;
            }
            case 'marker':
                return dist(pts[0], p) <= 10;
            case 'polyline':
            case 'path': {
                for (let i = 0; i < pts.length - 1; i++) {
                    if (pointToSegmentDist(p, pts[i], pts[i + 1]) <= tol) return true;
                }
                return false;
            }
            case 'angle':
                return pointToSegmentDist(p, pts[0], pts[1]) <= tol || pointToSegmentDist(p, pts[1], pts[2]) <= tol;
            case 'text': {
                const font = '16px system-ui';
                ctx.save();
                ctx.font = font;
                const w = ctx.measureText(shape.text || '').width;
                ctx.restore();
                const h = 18;
                const x = pts[0].x;
                const y = pts[0].y;
                return p.x >= x - 6 && p.x <= x + w + 6 && p.y >= y - h && p.y <= y + 6;
            }
            default:
                return false;
        }
    }

    function pointToSegmentDist(p, a, b) {
        const l2 = dist2(a, b);
        if (l2 === 0) return dist(p, a);
        let t = ((p.x - a.x) * (b.x - a.x) + (p.y - a.y) * (b.y - a.y)) / l2;
        t = Math.max(0, Math.min(1, t));
        const proj = { x: a.x + t * (b.x - a.x), y: a.y + t * (b.y - a.y) };
        return dist(p, proj);
    }
    const dist = (p1, p2) => Math.hypot(p1.x - p2.x, p1.y - p2.y);
    const dist2 = (p1, p2) => (p1.x - p2.x) ** 2 + (p1.y - p2.y) ** 2;

    /********************** 描画 **********************/
    function render(previewPoint = null) {
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, drawingCanvas.width, drawingCanvas.height);
        ctx.save();
        ctx.translate(state.pan.x, state.pan.y);

        state.shapes.forEach(s => drawShape(s, false));

        // プレビュー描画
        if (state.draft) drawShape(state.draft, true);
        if (state.tool === 'polyline' && state.polyWorking.length > 0) {
            const pts = [...state.polyWorking];
            if (previewPoint) pts.push(previewPoint);
            drawShape({ type: 'polyline', points: pts, color: state.color, stroke: state.stroke }, true);
        }
        if (state.tool === 'angle' && state.anglePoints.length > 0) {
            drawShape({ type: 'polyline', points: state.anglePoints, color: '#94a3b8', stroke: 2 }, true);
        }

        // ハンドル / 選択枠
        const sel = state.shapes.find(s => s.id === state.selectedId);
        if (sel) drawHandles(sel);

        ctx.restore();
    }

    function drawShape(shape, isPreview=false) {
        const c = shape.color || '#EA4335';
        const w = shape.stroke || 3;
        ctx.save();
        ctx.lineWidth = w;
        ctx.strokeStyle = c;
        ctx.fillStyle = c;

        const pts = shape.points;
        switch (shape.type) {
            case 'line':
                lineWithLabel(pts[0], pts[1], c, true);
                break;
            case 'arrow':
                drawArrowShape(pts[0], pts[1], c, w);
                break;
            case 'curve':
                drawCurveShape(pts[0], pts[1], c, w, shape.curveDir ?? 1, false);
                break;
            case 'curved-arrow':
                drawCurveShape(pts[0], pts[1], c, w, shape.curveDir ?? 1, true);
                break;
            case 'rectangle': {
                ctx.beginPath();
                ctx.rect(pts[0].x, pts[0].y, pts[1].x - pts[0].x, pts[1].y - pts[0].y);
                ctx.stroke();
                drawLabelSmall(`${Math.abs(pts[1].x-pts[0].x).toFixed(0)} × ${Math.abs(pts[1].y-pts[0].y).toFixed(0)} px`, (pts[0].x+pts[1].x)/2, pts[0].y - 12);
                break;
            }
            case 'circle': {
                const r = dist(pts[0], pts[1]);
                ctx.beginPath(); ctx.arc(pts[0].x, pts[0].y, r, 0, Math.PI * 2); ctx.stroke();
                drawLabelSmall(`r ${r.toFixed(0)} px`, pts[0].x, pts[0].y - r - 12);
                break;
            }
            case 'marker':
                drawCross(pts[0], c, w);
                break;
            case 'polyline':
            case 'path': {
                if (pts.length < 2) break;
                ctx.beginPath(); ctx.moveTo(pts[0].x, pts[0].y);
                for (let i = 1; i < pts.length; i++) ctx.lineTo(pts[i].x, pts[i].y);
                ctx.stroke();
                break;
            }
            case 'angle': {
                if (pts.length !== 3) break;
                drawAngleShape(pts[0], pts[1], pts[2], c, w);
                break;
            }
            case 'text': {
                const text = shape.text || '';
                ctx.font = '16px system-ui';
                ctx.fillStyle = c;
                ctx.textBaseline = 'alphabetic';
                ctx.textAlign = 'left';
                ctx.fillText(text, pts[0].x, pts[0].y);
                break;
            }
            default:
                break;
        }

        // プレビューは半透明
        if (isPreview) {
            ctx.globalAlpha = 0.35;
        }
        ctx.restore();
    }

    function drawArrowShape(a, b, color, w) {
        ctx.save();
        ctx.strokeStyle = color; ctx.fillStyle = color; ctx.lineWidth = w;
        ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
        const ang = Math.atan2(b.y - a.y, b.x - a.x);
        const len = 12 + w * 1.5;
        ctx.beginPath();
        ctx.moveTo(b.x, b.y);
        ctx.lineTo(b.x - len * Math.cos(ang - Math.PI / 8), b.y - len * Math.sin(ang - Math.PI / 8));
        ctx.lineTo(b.x - len * Math.cos(ang + Math.PI / 8), b.y - len * Math.sin(ang + Math.PI / 8));
        ctx.closePath();
        ctx.fill();
        ctx.restore();
    }

    function drawCurveShape(a, b, color, w, curveDir, withArrow) {
        ctx.save();
        ctx.strokeStyle = color; ctx.lineWidth = w; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
        const mid = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
        const off = { x: (b.y - a.y) * 0.28 * curveDir, y: (a.x - b.x) * 0.28 * curveDir };
        const cp = { x: mid.x + off.x, y: mid.y + off.y };
        ctx.beginPath();
        ctx.moveTo(a.x, a.y);
        ctx.quadraticCurveTo(cp.x, cp.y, b.x, b.y);
        ctx.stroke();
        if (withArrow) {
            const ang = Math.atan2(b.y - cp.y, b.x - cp.x);
            drawArrowHeadFilled(b, ang, color, w);
        }
        ctx.restore();
    }

    function drawArrowHeadFilled(p, ang, color, w) {
        ctx.save();
        ctx.fillStyle = color;
        const len = 12 + w * 1.5;
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        ctx.lineTo(p.x - len * Math.cos(ang - Math.PI / 8), p.y - len * Math.sin(ang - Math.PI / 8));
        ctx.lineTo(p.x - len * Math.cos(ang + Math.PI / 8), p.y - len * Math.sin(ang + Math.PI / 8));
        ctx.closePath();
        ctx.fill();
        ctx.restore();
    }

    function lineWithLabel(p1, p2, color, show) {
        ctx.save();
        ctx.strokeStyle = color; ctx.lineWidth = 3;
        ctx.beginPath(); ctx.moveTo(p1.x, p1.y); ctx.lineTo(p2.x, p2.y); ctx.stroke();
        if (show) {
            const dx = Math.abs(p2.x - p1.x).toFixed(0);
            drawLabelSmall(`T ${dx} px`, (p1.x + p2.x) / 2, (p1.y + p2.y) / 2 - 12);
        }
        ctx.restore();
    }

    function drawLabelSmall(text, x, y) {
        ctx.save();
        ctx.font = '13px "Roboto", "Noto Sans JP", sans-serif';
        const w = ctx.measureText(text).width;
        ctx.fillStyle = 'rgba(255,255,255,0.9)';
        ctx.strokeStyle = 'rgba(0,0,0,0.08)';
        ctx.lineWidth = 1;
        ctx.fillRect(x - w/2 - 8, y - 12, w + 16, 22);
        ctx.strokeRect(x - w/2 - 8, y - 12, w + 16, 22);
        ctx.fillStyle = '#0f172a';
        ctx.textBaseline = 'middle';
        ctx.textAlign = 'center';
        ctx.fillText(text, x, y - 1);
        ctx.restore();
    }

    function drawCross(p, color, w=3) {
        ctx.save();
        const size = 10;
        ctx.strokeStyle = color; ctx.lineWidth = w; ctx.lineCap = 'round';
        ctx.beginPath(); ctx.moveTo(p.x - size, p.y); ctx.lineTo(p.x + size, p.y); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(p.x, p.y - size); ctx.lineTo(p.x, p.y + size); ctx.stroke();
        ctx.restore();
    }

    function drawAngleShape(a, o, b, color, w) {
        ctx.save();
        ctx.strokeStyle = color; ctx.fillStyle = color; ctx.lineWidth = w; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
        ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(o.x, o.y); ctx.lineTo(b.x, b.y); ctx.stroke();
        ctx.beginPath(); ctx.arc(o.x, o.y, 5, 0, Math.PI * 2); ctx.fill();
        const angA = Math.atan2(a.y - o.y, a.x - o.x);
        const angB = Math.atan2(b.y - o.y, b.x - o.x);
        let sweep = angB - angA;
        if (sweep > Math.PI) sweep -= Math.PI * 2;
        if (sweep < -Math.PI) sweep += Math.PI * 2;
        const ccw = sweep < 0;
        const r = 38;
        ctx.globalAlpha = 0.2;
        ctx.beginPath(); ctx.moveTo(o.x, o.y); ctx.arc(o.x, o.y, r, angA, angB, ccw); ctx.closePath(); ctx.fill();
        ctx.globalAlpha = 1;
        ctx.beginPath(); ctx.arc(o.x, o.y, r, angA, angB, ccw); ctx.stroke();
        const deg = Math.abs(sweep) * 180 / Math.PI;
        const mid = angA + sweep / 2;
        drawLabelSmall(`${deg.toFixed(1)}°`, o.x + Math.cos(mid) * (r + 14), o.y + Math.sin(mid) * (r + 14));
        ctx.restore();
    }

    function drawHandles(shape) {
        ctx.save();
        ctx.translate(0, 0);
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = '#2563eb';
        ctx.lineWidth = 1.5;
        const handles = getHandles(shape);
        handles.forEach(pt => {
            ctx.beginPath();
            ctx.rect(pt.x - 6, pt.y - 6, 12, 12);
            ctx.fill();
            ctx.stroke();
        });
        ctx.restore();
    }

    /********************** 動画プレイヤー UI **********************/
    playBtn.addEventListener('click', () => {
        if (videoPlayer.paused) { videoPlayer.play(); playBtn.textContent = '⏸️'; }
        else { videoPlayer.pause(); playBtn.textContent = '▶️'; }
    });
    videoPlayer.addEventListener('timeupdate', () => {
        const progress = (videoPlayer.currentTime / videoPlayer.duration) * 100;
        progressFill.style.width = `${progress}%`;
        timeDisplay.textContent = formatTime(videoPlayer.currentTime) + ' / ' + formatTime(videoPlayer.duration);
    });
    progressBar.addEventListener('click', (e) => {
        const rect = progressBar.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        videoPlayer.currentTime = pos * videoPlayer.duration;
    });
    volumeSlider.addEventListener('input', (e) => videoPlayer.volume = e.target.value / 100);
    speedControl.addEventListener('click', () => {
        state.speedIndex = (state.speedIndex + 1) % speeds.length;
        videoPlayer.playbackRate = speeds[state.speedIndex];
        speedControl.textContent = `再生速度: ${speeds[state.speedIndex]}x`;
    });
    fullscreenBtn.addEventListener('click', () => videoContainer.requestFullscreen && videoContainer.requestFullscreen());
    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    /********************** タグ・メモ UI **********************/
    let currentTags = [];
    let currentMemos = [];
    let capturedImage = null;

    document.querySelectorAll('.predefined-tag').forEach(tag => {
        tag.addEventListener('click', () => {
            const name = tag.dataset.tag;
            if (tag.classList.contains('selected')) {
                tag.classList.remove('selected');
                currentTags = currentTags.filter(t => t !== name);
            } else {
                tag.classList.add('selected');
                currentTags.push(name);
            }
            updateSelectedTags();
        });
    });
    tagInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && tagInput.value.trim()) {
            const name = tagInput.value.trim();
            if (!currentTags.includes(name)) currentTags.push(name);
            tagInput.value = '';
            updateSelectedTags();
        }
    });
    function updateSelectedTags() {
        selectedTags.innerHTML = '';
        currentTags.forEach(tag => {
            const el = document.createElement('div');
            el.className = 'tag';
            el.innerHTML = `#${tag} <button class="tag-remove" onclick="removeTag('${tag}')">×</button>`;
            selectedTags.appendChild(el);
        });
    }
    window.removeTag = function(tag) {
        currentTags = currentTags.filter(t => t !== tag);
        document.querySelectorAll('.predefined-tag').forEach(el => { if (el.dataset.tag === tag) el.classList.remove('selected'); });
        updateSelectedTags();
    };

    saveMemoBtn.addEventListener('click', () => {
        const text = memoText.value.trim();
        if (!text) { alert('メモを入力してください'); return; }
        if (!capturedImage) capturedImage = captureFrame();
        const memo = { id: Date.now(), timestamp: videoPlayer.currentTime, text, tags: [...currentTags], image: capturedImage };
        currentMemos.push(memo);
        currentMemos.sort((a, b) => a.timestamp - b.timestamp);
        memoText.value = ''; currentTags = []; capturedImage = null;
        document.querySelectorAll('.predefined-tag').forEach(t => t.classList.remove('selected'));
        updateSelectedTags();
        renderMemos();
        saveMemoBtn.style.background = '#34A853'; saveMemoBtn.innerHTML = '<span>✓</span> 保存完了！';
        setTimeout(() => { saveMemoBtn.style.background = '#1967d2'; saveMemoBtn.innerHTML = '<span>💾</span> メモを保存'; }, 1500);
    });

    function renderMemos() {
        if (currentMemos.length === 0) {
            memoList.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📝</div><p>まだメモがありません</p><p style="font-size:12px; margin-top:10px;">動画をキャプチャしてメモを追加しましょう</p></div>';
            return;
        }
        memoList.innerHTML = '';
        currentMemos.forEach(m => {
            const el = document.createElement('div');
            el.className = 'memo-item';
            el.innerHTML = `
                <div class="memo-header"><div class="memo-timestamp">${formatTime(m.timestamp)}</div></div>
                <div class="memo-text">${m.text}</div>
                ${m.image ? `<img src="${m.image}" class="memo-thumbnail" alt="キャプチャ画像">` : ''}
                <div class="tag-list">${m.tags.map(t => `<div class="tag">#${t}</div>`).join('')}</div>
                <div class="memo-actions">
                    <button class="memo-action-btn" onclick="jumpToMemo(${m.timestamp})">📍 この時点に移動</button>
                    <button class="memo-action-btn" onclick="deleteMemo(${m.id})">🗑️ 削除</button>
                </div>`;
            memoList.appendChild(el);
        });
    }
    window.jumpToMemo = (ts) => { videoPlayer.currentTime = ts; videoPlayer.play(); playBtn.textContent = '⏸️'; };
    window.deleteMemo = (id) => {
        if (!confirm('このメモを削除しますか?')) return;
        currentMemos = currentMemos.filter(m => m.id !== id);
        renderMemos();
    };

    function captureFrame() {
        const temp = document.createElement('canvas');
        temp.width = videoPlayer.videoWidth || drawingCanvas.width;
        temp.height = videoPlayer.videoHeight || drawingCanvas.height;
        const tctx = temp.getContext('2d');
        tctx.drawImage(videoPlayer, 0, 0, temp.width, temp.height);
        // 現在のベクタをビットマップ描画
        render(); // 最新表示
        tctx.drawImage(drawingCanvas, 0, 0, temp.width, temp.height);
        return temp.toDataURL('image/png');
    }

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const tab = btn.dataset.tab;
            document.getElementById('memosTab').style.display = tab === 'memos' ? 'block' : 'none';
            document.getElementById('shareTab').style.display = tab === 'share' ? 'block' : 'none';
        });
    });

    shareBtn.addEventListener('click', () => {
        const demoUrl = `https://video-coaching-note.example.com/share/${Math.random().toString(36).slice(2, 9)}`;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(demoUrl);
            shareBtn.innerHTML = '<span>✓</span> リンクをコピーしました！';
            shareBtn.style.background = '#34A853';
            setTimeout(() => {
                shareBtn.innerHTML = '<span>🔗</span> リンクを生成';
                shareBtn.style.background = '#1967d2';
            }, 2500);
        } else {
            alert('デモURL: ' + demoUrl);
        }
    });

    // 初期化
    pushHistory();
    render();
    renderMemos();
    </script>
</x-layouts.plain>
