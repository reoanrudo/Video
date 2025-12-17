import { buildMapper, canvasToVideo } from "./canvas-mapper";
import { Renderer } from "./renderer";
import { EditorStore, createInitialAnalysis } from "./store";
import { hitTest, toVideoThreshold, withStyleThreshold } from "./hittest";
import { distance } from "./geometry";

const formatTime = (time) => {
  if (Number.isNaN(time) || time === Infinity) return "--:--";
  const total = Math.max(0, time);
  const m = Math.floor(total / 60);
  const s = Math.floor(total % 60);
  const ms = Math.floor((total % 1) * 100);
  return `${m}:${String(s).padStart(2, "0")}.${String(ms).padStart(2, "0")}`;
};

const getRoot = () => document.getElementById("editor-root");

const mapClientToVideo = (event, canvas, video) => {
  const mapper = buildMapper(canvas, video);
  const rect = canvas.getBoundingClientRect();
  const xCss = event.clientX - rect.left;
  const yCss = event.clientY - rect.top;
  const videoPoint = canvasToVideo(
    xCss * mapper.dpr,
    yCss * mapper.dpr,
    mapper.rect,
    mapper.dpr,
    mapper.videoWidth,
    mapper.videoHeight
  );
  const point = { x: videoPoint.vx, y: videoPoint.vy };
  return {
    point,
    mapper,
    pointer: {
      css: { x: xCss, y: yCss },
      canvas: { x: xCss * mapper.dpr, y: yCss * mapper.dpr },
      video: point,
    },
  };
};

const EditorApp = () => {
  const root = getRoot();
  if (!root) return;

  // Elements
  const canvas = document.getElementById("editor-canvas");
  const video = document.getElementById("editor-video");
  const videoInput = document.getElementById("video-input");
  const uploadArea = document.getElementById("upload-area");
  const videoControls = document.getElementById("video-controls");
  const videoName = document.getElementById("video-name");
  const saveBtn = document.getElementById("save-btn");
  const undoBtn = document.getElementById("undo-btn");
  const redoBtn = document.getElementById("redo-btn");
  const clearBtn = document.getElementById("clear-btn");
  const deleteBtn = document.getElementById("delete-btn");
  const selectionLabel = document.getElementById("selection-label");
  const toolButtons = document.querySelectorAll(".editor-tool-btn");
  const statusIndicator = document.getElementById("status-indicator");
  const styleColorInput = document.getElementById("style-color");
  const styleColorPicker = document.getElementById("style-color-picker");
  const styleWidthInput = document.getElementById("style-width");
  const styleOpacityInput = document.getElementById("style-opacity");
  const styleWidthValue = document.getElementById("style-width-value");
  const styleOpacityValue = document.getElementById("style-opacity-value");
  const keyframeBtn = document.getElementById("keyframe-btn");
  const keyframeList = document.getElementById("keyframe-list");
  const keyframeCount = document.getElementById("keyframe-count");
  const drawingList = document.getElementById("drawing-list");
  const drawingCount = document.getElementById("drawing-count");
  const currentTimeEl = document.getElementById("current-time");
  const durationEl = document.getElementById("duration");
  const seekBar = document.getElementById("seek-bar");
  const playBtn = document.getElementById("play-btn");
  const playIcon = document.getElementById("play-icon");
  const prevFrameBtn = document.getElementById("prev-frame");
  const nextFrameBtn = document.getElementById("next-frame");
  const speedSlider = document.getElementById("speed-slider");
  const speedVal = document.getElementById("speed-val");
  const timelineMarkers = document.getElementById("timeline-markers");
  const panelTabs = document.querySelectorAll(".editor-panel-tab");

  // Panels
  const keyframesPanel = document.getElementById("keyframes-panel");
  const drawingsPanel = document.getElementById("drawings-panel");
  const stylePanel = document.getElementById("style-panel");

  const initialAnalysis = root.dataset.analysis ? JSON.parse(root.dataset.analysis) : createInitialAnalysis();
  const store = new EditorStore(initialAnalysis);
  const debugState = {
    enabled: false,
    pointer: null,
    mapper: null,
    isDrawing: false,
  };
  const renderer = Renderer(canvas, video, store, () => store.selectedId, () => ({
    ...debugState,
    tool: store.currentTool,
  }));

  const sanitizeDrawings = () => {
    const before = store.analysis.drawings?.length ?? 0;
    const filtered = (store.analysis.drawings ?? []).filter((d) => d && typeof d.type === "string" && d.type.length > 0);
    if (filtered.length !== before) {
      store.analysis.drawings = filtered;
      if (store.selectedId && !filtered.find((d) => d.id === store.selectedId)) {
        store.select(null);
      }
    }
  };
  sanitizeDrawings();

  let isDrawing = false;
  let activeHandle = null;
  let editSnapshotTaken = false;
  let pendingNewDrawingId = null;
  let videoDuration = 0;
  let currentColor = "#3b82f6";
  let videoReady = video?.videoWidth > 0 && video?.videoHeight > 0;

  const setVideoReady = () => {
    videoReady = Boolean(video?.videoWidth && video?.videoHeight);
  };

  // Tab switching
  panelTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      panelTabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      const tabName = tab.dataset.tab;
      keyframesPanel?.classList.toggle("editor-hidden", tabName !== "keyframes");
      drawingsPanel?.classList.toggle("editor-hidden", tabName !== "drawings");
      stylePanel?.classList.toggle("editor-hidden", tabName !== "style");
    });
  });

  const updateStylePanel = () => {
    const drawing = store.getDrawing(store.selectedId);
    const disabled = !drawing;
    [styleColorPicker, styleWidthInput, styleOpacityInput].forEach((el) => {
      if (el) el.disabled = disabled;
    });
    if (!drawing) {
      if (styleWidthValue) styleWidthValue.textContent = "-";
      if (styleOpacityValue) styleOpacityValue.textContent = "-";
      return;
    }
    if (styleColorPicker) styleColorPicker.value = drawing.style?.color ?? "#3b82f6";
    if (styleWidthInput && styleWidthValue) {
      const lw = drawing.style?.lineWidth ?? 2;
      styleWidthInput.value = lw;
      styleWidthValue.textContent = `${lw}`;
    }
    if (styleOpacityInput && styleOpacityValue) {
      const op = drawing.style?.opacity ?? 1;
      styleOpacityInput.value = op;
      styleOpacityValue.textContent = op.toFixed(2);
    }
  };

  const updateSelectionLabel = () => {
    if (!store.selectedId) {
      if (selectionLabel) selectionLabel.textContent = "なし";
      updateStylePanel();
      return;
    }
    const d = store.analysis.drawings.find((x) => x.id === store.selectedId);
    if (selectionLabel) selectionLabel.textContent = d ? `${d.type} (${d.id.slice(0, 6)})` : "なし";
    updateStylePanel();
  };

  const setStatus = (text) => {
    if (statusIndicator) statusIndicator.textContent = text;
  };

  const markDirty = () => renderer();

  const setTool = (tool) => {
    store.setTool(tool);
    toolButtons.forEach((btn) => {
      btn.classList.toggle("active", btn.dataset.tool === tool);
    });
  };

  toolButtons.forEach((btn) => {
    btn.addEventListener("click", () => setTool(btn.dataset.tool));
  });

  // Color sync between toolbar and style panel
  styleColorInput?.addEventListener("input", (e) => {
    currentColor = e.target.value;
    if (styleColorPicker) styleColorPicker.value = currentColor;
    if (store.selectedId) {
      store.setStyle(store.selectedId, { color: currentColor });
      markDirty();
    }
  });

  styleColorPicker?.addEventListener("input", (e) => {
    currentColor = e.target.value;
    if (styleColorInput) styleColorInput.value = currentColor;
    if (store.selectedId) {
      store.setStyle(store.selectedId, { color: currentColor });
      markDirty();
    }
  });

  setTool("select");
  updateSelectionLabel();

  const maybeAddKeyframe = (time, source = "manual") => {
    const t = Number.isFinite(time) ? time : 0;
    const threshold = 0.3;
    const exists = store.analysis.keyframes.some((kf) => Math.abs((kf.time ?? 0) - t) <= threshold);
    if (exists) return null;
    const label = source === "auto" ? `Auto ${formatTime(t)}` : `Keyframe ${formatTime(t)}`;
    return store.addKeyframe(t, label);
  };

  const renderKeyframes = () => {
    if (!keyframeList) return;
    const list = store.analysis.keyframes ?? [];
    if (keyframeCount) keyframeCount.textContent = `${list.length}件`;

    if (list.length === 0) {
      keyframeList.innerHTML = `<div class="editor-empty">キーフレームがありません<br><small>動画再生中に「キーフレーム追加」で保存</small></div>`;
      updateTimelineMarkers();
      return;
    }

    keyframeList.innerHTML = list
      .slice()
      .sort((a, b) => (a.time ?? 0) - (b.time ?? 0))
      .map((kf) => `
        <div class="editor-keyframe-item" data-keyframe-id="${kf.id}" data-time="${kf.time ?? 0}">
          <div class="editor-keyframe-header">
            <div class="editor-keyframe-thumb"></div>
            <div class="editor-keyframe-info">
              <div class="editor-keyframe-time">${formatTime(kf.time ?? 0)}</div>
              <div class="editor-keyframe-title">${kf.label ?? "Keyframe"}</div>
            </div>
            <div class="editor-keyframe-actions">
              <button data-delete="${kf.id}" title="削除">×</button>
            </div>
          </div>
        </div>
      `).join("");

    updateTimelineMarkers();
  };

  const updateTimelineMarkers = () => {
    if (!timelineMarkers || videoDuration === 0) return;
    const list = store.analysis.keyframes ?? [];
    timelineMarkers.innerHTML = list.map((kf) => {
      const percent = ((kf.time ?? 0) / videoDuration) * 100;
      return `<div class="editor-timeline-marker" style="left:${percent}%" title="${kf.label ?? "Keyframe"} (${formatTime(kf.time ?? 0)})" data-time="${kf.time ?? 0}"></div>`;
    }).join("");
  };

  const renderDrawings = () => {
    if (!drawingList) return;
    sanitizeDrawings();
    const valid = store.analysis.drawings ?? [];

    if (drawingCount) drawingCount.textContent = `${valid.length}件`;

    const typeNames = {
      pen: "ペン",
      line: "直線",
      arrow: "矢印",
      circle: "円",
      marker: "マーカー",
      text: "テキスト",
      angle: "角度",
      autonumber: "番号",
      stamp: "スタンプ",
      track: "トラック",
      stopwatch: "ストップウォッチ",
    };

    if (valid.length === 0) {
      drawingList.innerHTML = `<div class="editor-empty">描画がありません</div>`;
      return;
    }

    drawingList.innerHTML = valid.map((d) => `
      <div class="editor-anno-item ${store.selectedId === d.id ? 'active' : ''}" data-drawing-id="${d.id}">
        <div class="editor-anno-color" style="background:${d.style?.color ?? '#3b82f6'}"></div>
        <span class="editor-anno-name">${typeNames[d.type] || d.type || "不明ツール"}</span>
        <button class="editor-anno-del" data-delete="${d.id}">×</button>
      </div>
    `).join("");
  };

  const updateVideoTime = () => {
    if (!video) return;
    const current = formatTime(video.currentTime ?? 0);
    const total = formatTime(video.duration ?? 0);
    if (currentTimeEl) currentTimeEl.textContent = current;
    if (durationEl) durationEl.textContent = total;
  };

  const beginEditSnapshot = () => {
    if (editSnapshotTaken) return;
    store.snapshot();
    editSnapshotTaken = true;
  };

  const resetEditSnapshot = () => {
    editSnapshotTaken = false;
  };

  const handleSelectHandle = (point, scale) => {
    if (!store.selectedId) return null;
    const drawing = store.analysis.drawings.find((d) => d.id === store.selectedId);
    if (!drawing) return null;

    const threshold = withStyleThreshold(scale, drawing);
    const dist = (p) => Math.hypot(p.x - point.x, p.y - point.y);

    switch (drawing.type) {
      case "line":
      case "arrow": {
        const pts = [drawing.geometry.start, drawing.geometry.end];
        const idx = pts.findIndex((p) => dist(p) <= threshold);
        if (idx !== -1) return { id: drawing.id, mode: idx === 0 ? "start" : "end" };
        break;
      }
      case "marker":
      case "text":
      case "autonumber":
        if (dist(drawing.geometry.position) <= threshold) return { id: drawing.id, mode: "move" };
        break;
      case "angle": {
        const idx = drawing.geometry.points.findIndex((p) => dist(p) <= threshold);
        if (idx !== -1) return { id: drawing.id, mode: idx };
        break;
      }
      case "pen": {
        const path = drawing.geometry.path ?? [];
        const idx = path.findIndex((p) => dist(p) <= threshold);
        if (idx !== -1) return { id: drawing.id, mode: "move" };
        break;
      }
      case "circle": {
        const center = drawing.geometry.center;
        const radius = drawing.geometry.radius ?? 0;
        const distCenter = distance(center, point);
        if (Math.abs(distCenter - radius) <= threshold) return { id: drawing.id, mode: "radius" };
        if (distCenter <= threshold) return { id: drawing.id, mode: "move" };
        break;
      }
      case "track": {
        if (hitTest(point, { ...drawing, type: "track" }, scale)) return { id: drawing.id, mode: "move" };
        break;
      }
      case "stamp":
        if (hitTest(point, { ...drawing, type: "stamp" }, scale)) return { id: drawing.id, mode: "move" };
        break;
    }
    return null;
  };

  const handlePointerDown = (e) => {
    if (!canvas || !video) return;
    if (!videoReady) {
      setStatus("動画読み込み中...");
      return;
    }
    const { point, mapper, pointer } = mapClientToVideo(e, canvas, video);
    debugState.pointer = pointer;
    debugState.mapper = mapper;
    const scale = mapper.rect.scale || 1;
    const tool = store.currentTool;
    pendingNewDrawingId = null;

    if (tool === "select") {
      const origin = store.analysis.origin ?? { x: 0, y: 0 };
      const distOrigin = Math.hypot(point.x - origin.x, point.y - origin.y);
      const threshold = toVideoThreshold(scale);
      if (distOrigin <= threshold) {
        activeHandle = { id: "origin", mode: "origin" };
        beginEditSnapshot();
        return;
      }

      const hit = [...store.analysis.drawings].reverse().find((d) => hitTest(point, d, scale));
      if (hit) {
        store.select(hit.id);
        const handle = handleSelectHandle(point, scale);
        activeHandle = handle ?? { id: hit.id, mode: "move" };
        beginEditSnapshot();
      } else {
        store.select(null);
        activeHandle = null;
      }
      updateSelectionLabel();
      renderDrawings();
      markDirty();
      return;
    }

    isDrawing = true;
    debugState.isDrawing = true;

    switch (tool) {
      case "shape": {
        const id = store.addDrawing({
          type: "circle",
          geometry: { center: point, radius: 0 },
          style: { color: currentColor },
        });
        store.select(id);
        pendingNewDrawingId = id;
        break;
      }
      case "stamp": {
        const id = store.addDrawing({
          type: "stamp",
          geometry: { position: point },
          style: { color: currentColor },
        });
        store.select(id);
        pendingNewDrawingId = id;
        isDrawing = false;
        break;
      }
      case "pen": {
        const id = store.addDrawing({ type: "pen", geometry: { path: [point] }, style: { color: currentColor } });
        store.select(id);
        pendingNewDrawingId = id;
        break;
      }
      case "line": {
        const id = store.addDrawing({
          type: "line",
          geometry: { start: point, end: point },
          style: { color: currentColor },
        });
        store.select(id);
        pendingNewDrawingId = id;
        break;
      }
      case "arrow": {
        const id = store.addDrawing({
          type: "arrow",
          geometry: { start: point, end: point },
          style: { color: currentColor },
        });
        store.select(id);
        pendingNewDrawingId = id;
        break;
      }
      case "marker":
        pendingNewDrawingId = store.addDrawing({ type: "marker", geometry: { position: point }, style: { color: currentColor } });
        store.select(pendingNewDrawingId);
        isDrawing = false;
        break;
      case "angle": {
        const id = store.addDrawing({
          type: "angle",
          geometry: { points: [point, point, point] },
          style: { color: currentColor },
        });
        store.select(id);
        pendingNewDrawingId = id;
        break;
      }
      case "text": {
        const content = prompt("テキストを入力してください", "Note");
        if (content) {
          const id = store.addDrawing({
            type: "text",
            geometry: { position: point, content },
            style: { color: currentColor },
          });
          store.select(id);
          pendingNewDrawingId = id;
        }
        isDrawing = false;
        break;
      }
    }
    updateSelectionLabel();
    renderDrawings();
    markDirty();
  };

  const handlePointerMove = (e) => {
    if (!canvas || !video || !videoReady) return;
    const { point, mapper, pointer } = mapClientToVideo(e, canvas, video);
    debugState.pointer = pointer;
    debugState.mapper = mapper;
    const selected = store.selectedId;

    if (activeHandle && activeHandle.mode === "origin") {
      store.setOrigin(point);
      markDirty();
      return;
    }

    if (activeHandle && selected) {
      beginEditSnapshot();
      store.updateDrawing(
        selected,
        (d) => {
          const g = d.geometry;
          switch (d.type) {
            case "line":
            case "arrow":
              if (activeHandle.mode === "move") {
                const dx = point.x - g.start.x;
                const dy = point.y - g.start.y;
                return { geometry: { ...g, start: point, end: { x: g.end.x + dx, y: g.end.y + dy } } };
              }
              return {
                geometry: {
                  ...g,
                  start: activeHandle.mode === "start" ? point : g.start,
                  end: activeHandle.mode === "end" ? point : g.end,
                },
              };
            case "marker":
            case "text":
            case "autonumber":
              return { geometry: { ...g, position: point } };
            case "angle": {
              if (activeHandle.mode === "move") {
                const dx = point.x - g.points[0].x;
                const dy = point.y - g.points[0].y;
                const moved = g.points.map((p) => ({ x: p.x + dx, y: p.y + dy }));
                return { geometry: { ...g, points: moved } };
              }
              const points = [...g.points];
              const idx = typeof activeHandle.mode === "number" ? activeHandle.mode : 0;
              points[idx] = point;
              return { geometry: { ...g, points } };
            }
            case "pen":
              if (activeHandle.mode === "move") {
                const dx = point.x - g.path[0].x;
                const dy = point.y - g.path[0].y;
                const moved = g.path.map((p) => ({ x: p.x + dx, y: p.y + dy }));
                return { geometry: { ...g, path: moved } };
              }
              return { geometry: g };
            case "circle": {
              if (activeHandle.mode === "move") {
                const dx = point.x - g.center.x;
                const dy = point.y - g.center.y;
                return {
                  geometry: {
                    ...g,
                    center: { x: g.center.x + dx, y: g.center.y + dy },
                  },
                };
              }
              if (activeHandle.mode === "radius") {
                const radius = Math.hypot(point.x - g.center.x, point.y - g.center.y);
                return { geometry: { ...g, radius } };
              }
              return { geometry: g };
            }
            case "track": {
              if (activeHandle.mode === "move") {
                const dx = point.x - g.path[0].x;
                const dy = point.y - g.path[0].y;
                const moved = g.path.map((p) => ({ x: p.x + dx, y: p.y + dy }));
                return { geometry: { ...g, path: moved } };
              }
              return { geometry: g };
            }
            case "stamp": {
              return { geometry: { ...g, position: point } };
            }
            default:
              return { geometry: g };
          }
        },
        { snapshot: false, replace: true }
      );
      markDirty();
      return;
    }

    if (!isDrawing || !selected) return;
    const drawing = store.analysis.drawings.find((d) => d.id === selected);
    if (!drawing) return;

    switch (drawing.type) {
      case "pen": {
        store.updateDrawing(
          selected,
          (d) => {
            const path = [...(d.geometry.path ?? [])];
            path.push(point);
            return { geometry: { ...d.geometry, path } };
          },
          { snapshot: false, replace: true }
        );
        break;
      }
      case "line":
      case "arrow":
        store.updateDrawing(
          selected,
          (d) => ({ geometry: { ...d.geometry, end: point } }),
          { snapshot: false, replace: true }
        );
        break;
      case "circle": {
        store.updateDrawing(
          selected,
          (d) => {
            const radius = Math.hypot(point.x - d.geometry.center.x, point.y - d.geometry.center.y);
            return { geometry: { ...d.geometry, radius } };
          },
          { snapshot: false, replace: true }
        );
        break;
      }
      case "angle": {
        store.updateDrawing(
          selected,
          (d) => {
            const points = [...d.geometry.points];
            points[1] = point;
            points[2] = point;
            return { geometry: { ...d.geometry, points } };
          },
          { snapshot: false, replace: true }
        );
        break;
      }
    }
    markDirty();
  };

  const handlePointerUp = () => {
    isDrawing = false;
    debugState.isDrawing = false;
    activeHandle = null;
    resetEditSnapshot();
    if (pendingNewDrawingId) {
      const time = video?.currentTime ?? 0;
      maybeAddKeyframe(time, "auto");
      pendingNewDrawingId = null;
      renderKeyframes();
      renderDrawings();
    }
  };

  canvas?.addEventListener("pointerdown", handlePointerDown);
  window.addEventListener("pointermove", handlePointerMove);
  window.addEventListener("pointerup", handlePointerUp);

  undoBtn?.addEventListener("click", () => {
    store.undo();
    markDirty();
    updateSelectionLabel();
    renderDrawings();
    renderKeyframes();
  });

  redoBtn?.addEventListener("click", () => {
    store.redo();
    markDirty();
    updateSelectionLabel();
    renderDrawings();
    renderKeyframes();
  });

  clearBtn?.addEventListener("click", () => {
    if (store.analysis.drawings.length === 0) return;
    if (confirm("すべての描画を消去しますか?")) {
      store.snapshot();
      store.analysis.drawings = [];
      store.select(null);
      markDirty();
      updateSelectionLabel();
      renderDrawings();
    }
  });

  deleteBtn?.addEventListener("click", () => {
    if (store.selectedId) {
      store.removeDrawing(store.selectedId);
      updateSelectionLabel();
      markDirty();
      renderDrawings();
      renderKeyframes();
    }
  });

  styleWidthInput?.addEventListener("input", (event) => {
    if (!store.selectedId) return;
    const value = Number(event.target.value);
    if (styleWidthValue) styleWidthValue.textContent = `${value}`;
    store.setStyle(store.selectedId, { lineWidth: value }, { snapshot: false });
    markDirty();
  });

  styleWidthInput?.addEventListener("change", (event) => {
    if (!store.selectedId) return;
    const value = Number(event.target.value);
    store.setStyle(store.selectedId, { lineWidth: value });
    markDirty();
  });

  styleOpacityInput?.addEventListener("input", (event) => {
    if (!store.selectedId) return;
    const value = Number(event.target.value);
    if (styleOpacityValue) styleOpacityValue.textContent = value.toFixed(2);
    store.setStyle(store.selectedId, { opacity: value }, { snapshot: false });
    markDirty();
  });

  styleOpacityInput?.addEventListener("change", (event) => {
    if (!store.selectedId) return;
    const value = Number(event.target.value);
    store.setStyle(store.selectedId, { opacity: value });
    markDirty();
  });

  saveBtn?.addEventListener("click", async () => {
    const putUrl = root.dataset.apiPut;
    setStatus("Saving...");
    const res = await fetch(putUrl, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content ?? "",
      },
      body: JSON.stringify({ analysis: store.analysis }),
    });
    setStatus(res.ok ? "Saved" : "Save failed");
  });

  // Video file input
  videoInput?.addEventListener("change", (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    video.src = url;
    video.load();
    if (videoName) videoName.textContent = file.name;
    if (uploadArea) uploadArea.style.display = "none";
    if (videoControls) videoControls.style.display = "block";
  });

  video?.addEventListener("loadedmetadata", () => {
    videoDuration = video.duration;
    if (seekBar) seekBar.max = videoDuration;
    markDirty();
    updateVideoTime();
    updateTimelineMarkers();
  });

  video?.addEventListener("timeupdate", () => {
    updateVideoTime();
    if (seekBar) seekBar.value = video.currentTime;
  });

  video?.addEventListener("ended", () => {
    if (playIcon) playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"/>';
  });

  playBtn?.addEventListener("click", () => {
    if (!video) return;
    if (video.paused) {
      video.play();
      if (playIcon) playIcon.innerHTML = '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
    } else {
      video.pause();
      if (playIcon) playIcon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"/>';
    }
  });

  seekBar?.addEventListener("input", () => {
    if (video) video.currentTime = parseFloat(seekBar.value);
  });

  prevFrameBtn?.addEventListener("click", () => {
    if (video) video.currentTime = Math.max(0, video.currentTime - 1 / 30);
  });

  nextFrameBtn?.addEventListener("click", () => {
    if (video) video.currentTime = Math.min(video.duration, video.currentTime + 1 / 30);
  });

  speedSlider?.addEventListener("input", () => {
    if (video) video.playbackRate = parseFloat(speedSlider.value);
    if (speedVal) speedVal.textContent = `${speedSlider.value}x`;
  });

  keyframeBtn?.addEventListener("click", () => {
    const time = video?.currentTime ?? 0;
    maybeAddKeyframe(time, "manual");
    renderKeyframes();
  });

  keyframeList?.addEventListener("click", (event) => {
    const deleteBtn = event.target.closest("button[data-delete]");
    if (deleteBtn) {
      const id = deleteBtn.dataset.delete;
      store.analysis.keyframes = store.analysis.keyframes.filter((kf) => kf.id !== id);
      renderKeyframes();
      return;
    }

    const item = event.target.closest(".editor-keyframe-item");
    if (!item) return;
    const time = Number(item.dataset.time ?? 0);
    if (video && Number.isFinite(time)) {
      video.currentTime = time;
    }
  });

  drawingList?.addEventListener("click", (event) => {
    const deleteBtn = event.target.closest("button[data-delete]");
    if (deleteBtn) {
      const id = deleteBtn.dataset.delete;
      store.removeDrawing(id);
      updateSelectionLabel();
      markDirty();
      renderDrawings();
      return;
    }

    const item = event.target.closest(".editor-anno-item");
    if (!item) return;
    const id = item.dataset.drawingId;
    store.select(id);
    updateSelectionLabel();
    markDirty();
    renderDrawings();
  });

  timelineMarkers?.addEventListener("click", (event) => {
    const marker = event.target.closest(".editor-timeline-marker");
    if (!marker) return;
    const time = Number(marker.dataset.time ?? 0);
    if (video && Number.isFinite(time)) {
      video.currentTime = time;
    }
  });

  window.addEventListener("resize", () => markDirty());

  // Keyboard shortcuts
  window.addEventListener("keydown", (e) => {
    if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") return;

    if ((e.metaKey || e.ctrlKey) && e.key === "z" && !e.shiftKey) {
      e.preventDefault();
      store.undo();
      markDirty();
      updateSelectionLabel();
      renderDrawings();
      renderKeyframes();
    }

    if ((e.metaKey || e.ctrlKey) && e.key === "z" && e.shiftKey) {
      e.preventDefault();
      store.redo();
      markDirty();
      updateSelectionLabel();
      renderDrawings();
      renderKeyframes();
    }

    if (e.key === "Delete" || e.key === "Backspace") {
      if (store.selectedId) {
        e.preventDefault();
        store.removeDrawing(store.selectedId);
        updateSelectionLabel();
        markDirty();
        renderDrawings();
      }
    }
  });

  // Initial render
  const bootstrap = async () => {
    const getUrl = root.dataset.apiGet;
    try {
      const res = await fetch(getUrl, { headers: { Accept: "application/json" } });
      if (res.ok) {
        const json = await res.json();
        if (json.analysis) {
          store.setAnalysis(json.analysis);
          sanitizeDrawings();
          store.select(null);
          updateSelectionLabel();
        }
      }
    } catch (e) {
      console.warn("Failed to load analysis", e);
    }
    markDirty();
    renderKeyframes();
    renderDrawings();
    updateVideoTime();
  };

  bootstrap();
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", EditorApp);
} else {
  EditorApp();
}
