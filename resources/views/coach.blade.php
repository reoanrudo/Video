<x-layouts.app :title="__('動画コーチングノート')">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Google Sans', 'Product Sans', 'Roboto', 'Noto Sans JP', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .container {
            max-width: 100%;
            height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .header {
            background: white;
            color: #5f6368;
            padding: 8px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e8eaed;
            height: 64px;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
            position: relative;
            z-index: 100;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #5f6368;
        }
        .header h1 .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4285f4 0%, #34a853 50%, #fbbc04 75%, #ea4335 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .header-icons {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .icon-btn {
            background: transparent;
            border: none;
            color: #5f6368;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .icon-btn:hover { background: rgba(60,64,67,0.08); }
        .icon-btn:active { background: rgba(60,64,67,0.12); }
        .main-content {
            display: grid;
            grid-template-columns: 72px 1fr 360px;
            height: calc(100vh - 64px);
            overflow: hidden;
        }
        .toolbar {
            background: #f8f9fa;
            border-right: 1px solid #dadce0;
            padding: 16px 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
            overflow-y: auto;
        }
        .tool-btn {
            width: 56px;
            height: 56px;
            border: none;
            background: transparent;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            position: relative;
        }
        .tool-btn:hover { background: rgba(60,64,67,0.08); }
        .tool-btn:active { background: rgba(60,64,67,0.12); }
        .tool-btn.active {
            background: #e8f0fe;
            color: #1967d2;
        }
        .tool-btn.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 32px;
            background: #1967d2;
            border-radius: 0 2px 2px 0;
        }
        .color-palette {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #dadce0;
        }
        .color-btn {
            width: 32px;
            height: 32px;
            border: 2px solid transparent;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        }
        .color-btn:hover {
            transform: scale(1.15);
            box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15);
        }
        .color-btn.active {
            border: 3px solid #1967d2;
            box-shadow: 0 0 0 1px white, 0 0 0 3px #1967d2;
        }
        .video-area {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow-y: auto;
            background: #f8f9fa;
        }
        .upload-zone {
            border: 2px dashed #dadce0;
            border-radius: 8px;
            padding: 80px 60px;
            text-align: center;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .upload-zone:hover { background: #f8f9fa; border-color: #1967d2; }
        .upload-zone.dragover { background: #e8f0fe; border-color: #1967d2; border-width: 3px; }
        .upload-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.6; }
        .upload-zone h2 { font-size: 20px; font-weight: 400; color: #202124; margin-bottom: 8px; }
        .upload-zone p { font-size: 14px; color: #5f6368; }
        .video-container { position: relative; background: #000; border-radius: 8px; overflow: hidden; display: none; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 2px 6px 2px rgba(60,64,67,0.15); }
        .video-container.active { display: block; }
        .canvas-wrapper { position: relative; }
        #videoPlayer { width: 100%; display: block; }
        #drawingCanvas { position: absolute; top: 0; left: 0; cursor: crosshair; }
        .video-controls {
            background: rgba(0,0,0,0.9);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }
        .play-btn { background: transparent; border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
        .play-btn:hover { background: rgba(255,255,255,0.1); }
        .time-display { font-size: 13px; min-width: 110px; font-family: 'Roboto Mono', monospace; }
        .progress-bar { flex: 1; height: 4px; background: rgba(255,255,255,0.3); border-radius: 2px; cursor: pointer; position: relative; transition: height 0.2s; }
        .progress-bar:hover { height: 6px; }
        .progress-fill { height: 100%; background: #1967d2; border-radius: 2px; width: 0%; transition: width 0.1s; }
        .volume-control { display: flex; align-items: center; gap: 8px; }
        .volume-slider { width: 70px; height: 4px; -webkit-appearance: none; background: rgba(255,255,255,0.3); border-radius: 2px; outline: none; }
        .volume-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; background: white; border-radius: 50%; cursor: pointer; }
        .speed-control { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500; transition: background 0.2s; }
        .speed-control:hover { background: rgba(255,255,255,0.2); }
        .capture-btn { background: #1967d2; border: none; color: white; padding: 8px 16px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; transition: all 0.2s; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); }
        .capture-btn:hover { background: #1557b0; box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15); }
        .capture-btn:active { background: #0d47a1; }
        .side-panel { background: white; border-left: 1px solid #dadce0; display: flex; flex-direction: column; overflow: hidden; }
        .panel-tabs { display: flex; background: white; border-bottom: 1px solid #dadce0; padding: 0 4px; }
        .tab-btn { flex: 1; padding: 14px 16px; border: none; background: none; cursor: pointer; font-size: 14px; font-weight: 500; color: #5f6368; border-bottom: 2px solid transparent; transition: all 0.2s; }
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
        .tag-remove:hover { background: rgba(25, 103, 210, 0.1); }
        .memo-list { display: flex; flex-direction: column; gap: 12px; }
        .memo-item { background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); cursor: pointer; transition: all 0.2s; border-left: 3px solid #1967d2; }
        .memo-item:hover { box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15); }
        .memo-timestamp { background: #1967d2; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 500; font-family: 'Roboto Mono', monospace; }
        .memo-actions { display: flex; gap: 8px; margin-top: 12px; }
        .memo-action-btn { background: #f8f9fa; border: 1px solid #dadce0; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; color: #5f6368; transition: all 0.2s; font-weight: 500; }
        .memo-action-btn:hover { background: #f1f3f4; border-color: #5f6368; }
        .empty-state { text-align: center; padding: 48px 24px; color: #5f6368; }
        .empty-state-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }
        @media (max-width: 1200px) { .main-content { grid-template-columns: 60px 1fr 300px; } }
        @media (max-width: 900px) {
            .main-content { grid-template-columns: 1fr; grid-template-rows: auto 1fr auto; }
            .toolbar { flex-direction: row; padding: 10px; overflow-x: auto; }
            .side-panel { max-height: 400px; }
        }
        ::-webkit-scrollbar { width: 12px; height: 12px; }
        ::-webkit-scrollbar-thumb { background: #dadce0; border-radius: 6px; border: 3px solid transparent; background-clip: content-box; }
        ::-webkit-scrollbar-thumb:hover { background: #bdc1c6; background-clip: content-box; }
    </style>

    <div class="container">
        <div class="header">
            <h1>
                <div class="logo-icon">🎥</div>
                動画コーチングノート
            </h1>
            <div class="header-icons">
                <button class="icon-btn" title="検索">🔍</button>
                <button class="icon-btn" title="通知">🔔</button>
                <button class="icon-btn" title="プロフィール">👤</button>
            </div>
        </div>

        <div class="main-content">
            <div class="toolbar" id="toolbar">
                <button class="tool-btn active" data-tool="select" title="選択"><span>↖️</span></button>
                <button class="tool-btn" data-tool="pen" title="ペン"><span>✏️</span></button>
                <button class="tool-btn" data-tool="bezier" title="ベジェ曲線（パス）"><span>🖊️</span></button>
                <button class="tool-btn" data-tool="line" title="直線"><span>📏</span></button>
                <button class="tool-btn" data-tool="curve" title="曲線"><span>〰️</span></button>
                <button class="tool-btn" data-tool="circle" title="円"><span>⭕</span></button>
                <button class="tool-btn" data-tool="arrow" title="矢印"><span>➡️</span></button>
                <button class="tool-btn" data-tool="curved-arrow" title="曲線矢印"><span>↪️</span></button>
                <button class="tool-btn" id="flipCurveBtn" title="曲線の向きを反転"><span>🔄</span></button>
                <button class="tool-btn" data-tool="text" title="テキスト"><span>T</span></button>
                <button class="tool-btn" data-tool="eraser" title="消しゴム"><span>🧽</span></button>
                <div style="height:20px; border-top:1px solid #dadce0; width:100%; margin:8px 0;"></div>
                <button class="tool-btn" id="undoBtn" title="元に戻す"><span>↶</span></button>
                <button class="tool-btn" id="redoBtn" title="やり直す"><span>↷</span></button>
                <button class="tool-btn" id="clearBtn" title="すべてクリア"><span>🗑️</span></button>
                <div style="height:20px;"></div>
                <div class="color-palette" id="colorPalette">
                    <div class="color-btn active" data-color="#EA4335" style="background:#EA4335;" title="赤"></div>
                    <div class="color-btn" data-color="#34A853" style="background:#34A853;" title="緑"></div>
                    <div class="color-btn" data-color="#4285F4" style="background:#4285F4;" title="青"></div>
                    <div class="color-btn" data-color="#FBBC04" style="background:#FBBC04;" title="黄"></div>
                    <div class="color-btn" data-color="#9C27B0" style="background:#9C27B0;" title="紫"></div>
                    <div class="color-btn" data-color="#FF6D00" style="background:#FF6D00;" title="橙"></div>
                    <div class="color-btn" data-color="#FFFFFF" style="background:#FFFFFF; border:1px solid #dadce0;" title="白"></div>
                    <div class="color-btn" data-color="#202124" style="background:#202124;" title="黒"></div>
                </div>
            </div>

            <div class="video-area">
                <div class="upload-zone" id="uploadZone">
                    <div class="upload-icon">📁</div>
                    <h2>動画をアップロード</h2>
                    <p>ドラッグ&ドロップまたはクリックしてファイルを選択</p>
                    <input type="file" id="videoInput" accept="video/*" style="display:none;">
                </div>

                <div class="video-container" id="videoContainer">
                    <div class="canvas-wrapper">
                        <video id="videoPlayer"></video>
                        <canvas id="drawingCanvas"></canvas>
                    </div>
                    <div class="video-controls">
                        <button class="play-btn" id="playBtn">▶️</button>
                        <div class="time-display" id="timeDisplay">0:00 / 0:00</div>
                        <div class="progress-bar" id="progressBar"><div class="progress-fill" id="progressFill"></div></div>
                        <div class="volume-control">
                            <span>🔊</span>
                            <input type="range" class="volume-slider" id="volumeSlider" min="0" max="100" value="100">
                        </div>
                        <button class="speed-control" id="speedControl">再生速度: 1x</button>
                        <button class="capture-btn" id="captureBtn"><span>📸</span> この瞬間をキャプチャ</button>
                        <button class="icon-btn" id="fullscreenBtn">⛶</button>
                    </div>
                </div>
            </div>

            <div class="side-panel">
                <div class="panel-tabs">
                    <button class="tab-btn active" data-tab="memos">メモ</button>
                    <button class="tab-btn" data-tab="share">共有</button>
                </div>
                <div class="panel-content">
                    <div id="memosTab" class="tab-content">
                        <div class="memo-form">
                            <textarea class="memo-textarea" id="memoText" placeholder="このショートフォームは改善が見られます。特に腕の..."></textarea>
                            <div class="tag-section">
                                <label>タグを選択</label>
                                <div class="tag-manager">
                                    <div class="predefined-tags" id="predefinedTags">
                                        <div class="predefined-tag" data-tag="フォーム">#フォーム</div>
                                        <div class="predefined-tag" data-tag="改善点">#改善点</div>
                                        <div class="predefined-tag" data-tag="良い点">#良い点</div>
                                        <div class="predefined-tag" data-tag="重要">#重要</div>
                                    </div>
                                </div>
                                <div class="tag-input-wrapper">
                                    <input type="text" class="tag-input" id="tagInput" placeholder="カスタムタグを追加...">
                                </div>
                                <div class="tag-list" id="selectedTags"></div>
                            </div>
                            <button class="save-memo-btn" id="saveMemoBtn"><span>💾</span> メモを保存</button>
                        </div>
                        <div class="memo-list" id="memoList">
                            <div class="empty-state">
                                <div class="empty-state-icon">📝</div>
                                <p>まだメモがありません</p>
                                <p style="font-size:12px; margin-top:10px;">動画をキャプチャしてメモを追加しましょう</p>
                            </div>
                        </div>
                    </div>
                    <div id="shareTab" class="tab-content" style="display:none;">
                        <div class="share-section">
                            <button class="share-btn" id="shareBtn"><span>🔗</span> リンクを生成</button>
                            <div class="share-info">
                                作成したコーチングノートは、リンク一つで簡単に共有可能。<br><br>
                                <strong>※ 現在はデモ版のため、実際の共有機能は未実装です</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 状態
        let currentTool = 'select';
        let currentColor = '#EA4335';
        let isDrawing = false;
        let lastX = 0, lastY = 0;
        let tempStartX = 0, tempStartY = 0;
        let tempSnapshot = null;
        let curveDirection = 1;
        let drawingHistory = [];
        let redoHistory = [];
        let capturedImage = null;
        let currentMemos = [];
        let currentTags = [];
        let speedIndex = 2;
        const speeds = [0.25, 0.5, 1, 1.5, 2];

        // 要素
        const videoPlayer   = document.getElementById('videoPlayer');
        const drawingCanvas = document.getElementById('drawingCanvas');
        const ctx           = drawingCanvas.getContext('2d');
        const videoInput    = document.getElementById('videoInput');
        const uploadZone    = document.getElementById('uploadZone');
        const videoContainer= document.getElementById('videoContainer');
        const playBtn       = document.getElementById('playBtn');
        const progressBar   = document.getElementById('progressBar');
        const progressFill  = document.getElementById('progressFill');
        const timeDisplay   = document.getElementById('timeDisplay');
        const volumeSlider  = document.getElementById('volumeSlider');
        const speedControl  = document.getElementById('speedControl');
        const captureBtn    = document.getElementById('captureBtn');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const memoText      = document.getElementById('memoText');
        const tagInput      = document.getElementById('tagInput');
        const selectedTags  = document.getElementById('selectedTags');
        const saveMemoBtn   = document.getElementById('saveMemoBtn');
        const memoList      = document.getElementById('memoList');
        const shareBtn      = document.getElementById('shareBtn');

        // アップロード
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
                saveDrawingState(); // 初期状態
            }, { once: true });
        }

        function resizeCanvas() {
            const rect = videoPlayer.getBoundingClientRect();
            drawingCanvas.style.width  = rect.width + 'px';
            drawingCanvas.style.height = rect.height + 'px';
        }
        window.addEventListener('resize', resizeCanvas);

        function saveDrawingState() {
            const imageData = ctx.getImageData(0, 0, drawingCanvas.width, drawingCanvas.height);
            drawingHistory.push(imageData);
            if (drawingHistory.length > 20) drawingHistory.shift();
            redoHistory = [];
        }
        function restoreDrawingState(imageData) { ctx.putImageData(imageData, 0, 0); }

        // ビデオコントロール
        playBtn.addEventListener('click', () => {
            if (videoPlayer.paused) { videoPlayer.play(); playBtn.textContent = '⏸️'; }
            else { videoPlayer.pause(); playBtn.textContent = '▶️'; }
        });
        videoPlayer.addEventListener('timeupdate', () => {
            const progress = (videoPlayer.currentTime / videoPlayer.duration) * 100;
            progressFill.style.width = progress + '%';
            timeDisplay.textContent = formatTime(videoPlayer.currentTime) + ' / ' + formatTime(videoPlayer.duration);
        });
        progressBar.addEventListener('click', (e) => {
            const rect = progressBar.getBoundingClientRect();
            const pos = (e.clientX - rect.left) / rect.width;
            videoPlayer.currentTime = pos * videoPlayer.duration;
        });
        volumeSlider.addEventListener('input', (e) => { videoPlayer.volume = e.target.value / 100; });
        speedControl.addEventListener('click', () => {
            speedIndex = (speedIndex + 1) % speeds.length;
            videoPlayer.playbackRate = speeds[speedIndex];
            speedControl.textContent = `再生速度: ${speeds[speedIndex]}x`;
        });
        fullscreenBtn.addEventListener('click', () => { if (videoContainer.requestFullscreen) videoContainer.requestFullscreen(); });
        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        // ツール選択
        document.querySelectorAll('.tool-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (['undoBtn','redoBtn','clearBtn','flipCurveBtn'].includes(btn.id)) return;
                document.querySelectorAll('.tool-btn').forEach(b => { if (!b.id) b.classList.remove('active'); });
                btn.classList.add('active');
                currentTool = btn.dataset.tool;
            });
        });
        document.getElementById('flipCurveBtn').addEventListener('click', () => {
            curveDirection *= -1;
            const btn = document.getElementById('flipCurveBtn');
            btn.style.background = curveDirection === 1 ? 'transparent' : '#e8f0fe';
            btn.title = curveDirection === 1 ? '曲線の向きを反転' : '曲線の向き: 反転中';
        });
        document.getElementById('undoBtn').addEventListener('click', () => {
            if (drawingHistory.length > 1) {
                redoHistory.push(drawingHistory.pop());
                restoreDrawingState(drawingHistory[drawingHistory.length - 1]);
            } else if (drawingHistory.length === 1) {
                redoHistory.push(drawingHistory.pop());
                ctx.clearRect(0, 0, drawingCanvas.width, drawingCanvas.height);
            }
        });
        document.getElementById('redoBtn').addEventListener('click', () => {
            if (redoHistory.length > 0) {
                const state = redoHistory.pop();
                drawingHistory.push(state);
                restoreDrawingState(state);
            }
        });
        document.getElementById('clearBtn').addEventListener('click', () => {
            if (confirm('描画をすべてクリアしますか？')) {
                ctx.clearRect(0, 0, drawingCanvas.width, drawingCanvas.height);
                drawingHistory = [];
                redoHistory = [];
                saveDrawingState();
            }
        });
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentColor = btn.dataset.color;
            });
        });

        // 描画イベント
        drawingCanvas.addEventListener('mousedown', startDrawing);
        drawingCanvas.addEventListener('mousemove', draw);
        drawingCanvas.addEventListener('mouseup', stopDrawing);
        drawingCanvas.addEventListener('mouseout', stopDrawing);

        function startDrawing(e) {
            if (currentTool === 'select') return;
            isDrawing = true;
            const { x, y } = toCanvasPos(e);
            tempStartX = lastX = x;
            tempStartY = lastY = y;
            if (['line','circle','arrow','curve','curved-arrow'].includes(currentTool)) {
                tempSnapshot = ctx.getImageData(0, 0, drawingCanvas.width, drawingCanvas.height);
            }
        }
        function draw(e) {
            if (!isDrawing || currentTool === 'select') return;
            const { x, y } = toCanvasPos(e);
            ctx.strokeStyle = currentColor;
            ctx.lineWidth = 3;
            ctx.lineCap = ctx.lineJoin = 'round';
            switch (currentTool) {
                case 'pen':
                    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y); ctx.stroke();
                    lastX = x; lastY = y; break;
                case 'line':
                    ctx.putImageData(tempSnapshot, 0, 0);
                    ctx.beginPath(); ctx.moveTo(tempStartX, tempStartY); ctx.lineTo(x, y); ctx.stroke(); break;
                case 'curve':
                case 'curved-arrow': {
                    ctx.putImageData(tempSnapshot, 0, 0);
                    const midX = (tempStartX + x) / 2, midY = (tempStartY + y) / 2;
                    const offX = (y - tempStartY) * 0.3 * curveDirection;
                    const offY = (tempStartX - x) * 0.3 * curveDirection;
                    const cpX = midX + offX, cpY = midY + offY;
                    ctx.beginPath(); ctx.moveTo(tempStartX, tempStartY); ctx.quadraticCurveTo(cpX, cpY, x, y); ctx.stroke();
                    if (currentTool === 'curved-arrow') drawArrowHead(x, y, Math.atan2(y - cpY, x - cpX));
                    break;
                }
                case 'circle':
                    ctx.putImageData(tempSnapshot, 0, 0);
                    const r = Math.hypot(x - tempStartX, y - tempStartY);
                    ctx.beginPath(); ctx.arc(tempStartX, tempStartY, r, 0, Math.PI * 2); ctx.stroke(); break;
                case 'arrow':
                    ctx.putImageData(tempSnapshot, 0, 0);
                    drawArrow(tempStartX, tempStartY, x, y); break;
                case 'eraser':
                    ctx.clearRect(x - 10, y - 10, 20, 20); break;
            }
        }
        function stopDrawing() {
            if (isDrawing && currentTool !== 'select') saveDrawingState();
            isDrawing = false;
            tempSnapshot = null;
        }
        function toCanvasPos(e) {
            const rect = drawingCanvas.getBoundingClientRect();
            const scaleX = drawingCanvas.width  / rect.width;
            const scaleY = drawingCanvas.height / rect.height;
            return { x: (e.clientX - rect.left) * scaleX, y: (e.clientY - rect.top) * scaleY };
        }
        function drawArrow(fromX, fromY, toX, toY) {
            const angle = Math.atan2(toY - fromY, toX - fromX);
            ctx.beginPath(); ctx.moveTo(fromX, fromY); ctx.lineTo(toX, toY); ctx.stroke();
            drawArrowHead(toX, toY, angle);
        }
        function drawArrowHead(x, y, angle) {
            const headlen = 20;
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.lineTo(x - headlen * Math.cos(angle - Math.PI / 6), y - headlen * Math.sin(angle - Math.PI / 6));
            ctx.moveTo(x, y);
            ctx.lineTo(x - headlen * Math.cos(angle + Math.PI / 6), y - headlen * Math.sin(angle + Math.PI / 6));
            ctx.stroke();
        }

        // タグとメモ（簡易）
        document.querySelectorAll('.predefined-tag').forEach(tag => {
            tag.addEventListener('click', () => {
                const tagName = tag.dataset.tag;
                if (tag.classList.contains('selected')) {
                    tag.classList.remove('selected');
                    currentTags = currentTags.filter(t => t !== tagName);
                } else {
                    tag.classList.add('selected');
                    currentTags.push(tagName);
                }
                updateSelectedTags();
            });
        });
        tagInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && tagInput.value.trim()) {
                const tagName = tagInput.value.trim();
                if (!currentTags.includes(tagName)) currentTags.push(tagName);
                tagInput.value = '';
                updateSelectedTags();
            }
        });
        function updateSelectedTags() {
            selectedTags.innerHTML = '';
            currentTags.forEach(tag => {
                const tagEl = document.createElement('div');
                tagEl.className = 'tag';
                tagEl.innerHTML = `#${tag} <button class="tag-remove" onclick="removeTag('${tag}')">×</button>`;
                selectedTags.appendChild(tagEl);
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
            const memo = {
                id: Date.now(),
                timestamp: videoPlayer.currentTime,
                text,
                tags: [...currentTags],
                image: capturedImage,
            };
            currentMemos.push(memo);
            currentMemos.sort((a, b) => a.timestamp - b.timestamp);
            memoText.value = '';
            currentTags = [];
            capturedImage = null;
            document.querySelectorAll('.predefined-tag').forEach(tag => tag.classList.remove('selected'));
            updateSelectedTags();
            renderMemos();
            saveMemoBtn.style.background = '#34A853';
            saveMemoBtn.innerHTML = '<span>✓</span> 保存完了！';
            setTimeout(() => {
                saveMemoBtn.style.background = '#1967d2';
                saveMemoBtn.innerHTML = '<span>💾</span> メモを保存';
            }, 2000);
        });
        function renderMemos() {
            if (currentMemos.length === 0) {
                memoList.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📝</div><p>まだメモがありません</p><p style="font-size:12px; margin-top:10px;">動画をキャプチャしてメモを追加しましょう</p></div>';
                return;
            }
            memoList.innerHTML = '';
            currentMemos.forEach(memo => {
                const el = document.createElement('div');
                el.className = 'memo-item';
                el.innerHTML = `
                    <div class="memo-header"><div class="memo-timestamp">${formatTime(memo.timestamp)}</div></div>
                    <div class="memo-text">${memo.text}</div>
                    ${memo.image ? `<img src="${memo.image}" class="memo-thumbnail" alt="キャプチャ画像" style="width:100%; height:120px; object-fit:cover; border-radius:4px; margin-top:12px;">` : ''}
                    <div class="tag-list">${memo.tags.map(t => `<div class="tag">#${t}</div>`).join('')}</div>
                    <div class="memo-actions">
                        <button class="memo-action-btn" onclick="jumpToMemo(${memo.timestamp})">📍 この時点に移動</button>
                        <button class="memo-action-btn" onclick="deleteMemo(${memo.id})">🗑️ 削除</button>
                    </div>
                `;
                memoList.appendChild(el);
            });
        }
        window.jumpToMemo = function(ts) { videoPlayer.currentTime = ts; videoPlayer.play(); playBtn.textContent = '⏸️'; };
        window.deleteMemo = function(id) {
            if (confirm('このメモを削除しますか?')) {
                currentMemos = currentMemos.filter(m => m.id !== id);
                renderMemos();
            }
        };

        function captureFrame() {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = videoPlayer.videoWidth || drawingCanvas.width;
            tempCanvas.height = videoPlayer.videoHeight || drawingCanvas.height;
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.drawImage(videoPlayer, 0, 0, tempCanvas.width, tempCanvas.height);
            tempCtx.drawImage(drawingCanvas, 0, 0, tempCanvas.width, tempCanvas.height);
            return tempCanvas.toDataURL('image/png');
        }

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const tab = btn.dataset.tab;
                document.getElementById('memosTab').style.display  = tab === 'memos' ? 'block' : 'none';
                document.getElementById('shareTab').style.display  = tab === 'share' ? 'block' : 'none';
            });
        });

        shareBtn.addEventListener('click', () => {
            const demoUrl = `https://video-coaching-note.example.com/share/${Math.random().toString(36).substr(2, 9)}`;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(demoUrl);
                shareBtn.innerHTML = '<span>✓</span> リンクをコピーしました！';
                shareBtn.style.background = '#34A853';
                setTimeout(() => {
                    shareBtn.innerHTML = '<span>🔗</span> リンクを生成';
                    shareBtn.style.background = '#1967d2';
                }, 3000);
            } else {
                alert('デモURL: ' + demoUrl);
            }
        });

        renderMemos();
    </script>
</x-layouts.app>
