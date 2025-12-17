const deepCopy = (obj) => JSON.parse(JSON.stringify(obj));

const DEFAULT_STYLE = {
  color: "#2563eb",
  lineWidth: 2,
  opacity: 1,
  dash: false,
  dashLength: 8,
  dashGap: 8,
  squiggleAmplitude: 4,
  squiggleWavelength: 12,
  curvature: 0.3,
};

const createInitialAnalysis = () => ({
  schema: { name: "videocoach.analysis", version: "1.0.0" },
  drawings: [],
  keyframes: [],
  extensions: { kvaPassthrough: {} },
  origin: { x: 0, y: 0 },
  meta: {},
});

const normalizeDrawing = (drawing) => {
  const withVariant = { ...drawing };
  if (withVariant.type === "arrow" && !withVariant.variant) withVariant.variant = "normal";
  if (withVariant.type === "angle" && !withVariant.variant) withVariant.variant = "three_point";
   if (withVariant.type === "text") {
    withVariant.geometry = {
      content: "Text",
      fontSize: 16,
      ...(withVariant.geometry ?? {}),
    };
  }
  if (withVariant.type === "stamp") {
    withVariant.geometry = {
      name: "★",
      ...(withVariant.geometry ?? {}),
    };
  }
  return {
    id: withVariant.id ?? crypto.randomUUID(),
    type: withVariant.type,
    variant: withVariant.variant,
    geometry: withVariant.geometry ?? {},
    style: { ...DEFAULT_STYLE, ...(withVariant.style ?? {}) },
  };
};

const normalizeAnalysis = (analysis) => {
  const base = analysis ?? createInitialAnalysis();

  return {
    schema: base.schema ?? { name: "videocoach.analysis", version: "1.0.0" },
    drawings: (base.drawings ?? [])
      .filter((d) => d && d.type) // 型が不明なものは破棄して描画を壊さない
      .map((d) => normalizeDrawing(d)),
    keyframes: base.keyframes ?? [],
    extensions: base.extensions ?? { kvaPassthrough: {} },
    origin: base.origin ?? { x: 0, y: 0 },
    meta: base.meta ?? {},
  };
};

class EditorStore {
  constructor(initialAnalysis = null) {
    this.analysis = normalizeAnalysis(initialAnalysis);
    this.undoStack = [];
    this.redoStack = [];
    this.selectedId = null;
    this.currentTool = "select";
  }

  setTool(tool) {
    this.currentTool = tool;
  }

  snapshot() {
    this.undoStack.push(deepCopy(this.analysis));
    this.redoStack = [];
  }

  undo() {
    if (this.undoStack.length === 0) return;
    this.redoStack.push(deepCopy(this.analysis));
    this.analysis = this.undoStack.pop();
  }

  redo() {
    if (this.redoStack.length === 0) return;
    this.undoStack.push(deepCopy(this.analysis));
    this.analysis = this.redoStack.pop();
  }

  setAnalysis(data) {
    this.analysis = normalizeAnalysis(data);
    this.undoStack = [];
    this.redoStack = [];
  }

  addDrawing(drawing) {
    this.snapshot();
    const normalized = normalizeDrawing({ id: crypto.randomUUID(), ...drawing });
    this.analysis.drawings.push(normalized);
    return normalized.id;
  }

  updateDrawing(id, updater, options = {}) {
    const { snapshot = true, replace = false } = options;
    const idx = this.analysis.drawings.findIndex((d) => d.id === id);
    if (idx === -1) return null;
    if (snapshot) this.snapshot();

    const current = this.analysis.drawings[idx];
    const updated = updater(deepCopy(current));
    const next = replace ? updated : { ...current, ...updated };

    this.analysis.drawings.splice(idx, 1, normalizeDrawing(next));
    return this.analysis.drawings[idx];
  }

  getDrawing(id) {
    return this.analysis.drawings.find((d) => d.id === id) ?? null;
  }

  removeDrawing(id) {
    const idx = this.analysis.drawings.findIndex((d) => d.id === id);
    if (idx === -1) return;
    this.snapshot();
    this.analysis.drawings.splice(idx, 1);
    if (this.selectedId === id) this.selectedId = null;
  }

  select(id) {
    this.selectedId = id;
  }

  setStyle(id, style, options = {}) {
    const { snapshot = true } = options;
    return this.updateDrawing(
      id,
      (d) => ({ style: { ...d.style, ...style } }),
      { snapshot, replace: false },
    );
  }

  addKeyframe(time, label = null) {
    this.snapshot();
    const keyframe = {
      id: crypto.randomUUID(),
      time,
      label: label ?? `Keyframe ${this.analysis.keyframes.length + 1}`,
    };
    this.analysis.keyframes.push(keyframe);
    return keyframe.id;
  }

  removeKeyframe(id) {
    const idx = this.analysis.keyframes.findIndex((k) => k.id === id);
    if (idx === -1) return;
    this.snapshot();
    this.analysis.keyframes.splice(idx, 1);
  }

  setOrigin(point) {
    this.snapshot();
    this.analysis.origin = { x: point.x, y: point.y };
  }
}

export { EditorStore, createInitialAnalysis, DEFAULT_STYLE, normalizeAnalysis };
