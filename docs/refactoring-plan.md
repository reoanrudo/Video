# Editor リファクタリング計画

## 概要
Option C: ツールハンドラー分離 + Shape Registry パターン + パン/ズーム対応

## 現状の問題点

1. **index.js が 1,176行のモノリシック**
   - ツール切替、描画作成、編集を巨大 switch 文で処理
   - 新しいツール追加時に複数箇所を修正する必要

2. **switch 文の重複**
   - renderer.js: 描画タイプごとに switch
   - hittest.js: 描画タイプごとに switch
   - index.js: ツール/描画タイプごとに switch

3. **パン/ズームなし**
   - 現在は object-fit: contain 固定

---

## Phase 1: Shape Registry パターン (1日目)

### 1.1 新しいファイル構造

```
resources/js/editor/
├── shapes/
│   ├── registry.js      # Shape登録・取得
│   ├── base.js          # 共通インターフェース定義
│   ├── pen.js
│   ├── line.js
│   ├── arrow.js
│   ├── marker.js
│   ├── angle.js
│   ├── circle.js
│   ├── text.js
│   ├── stamp.js
│   ├── track.js
│   └── stopwatch.js
├── tools/               # Phase 2 で追加
├── canvas-mapper.js     # 既存
├── geometry.js          # 既存
├── hittest.js           # registry経由に変更
├── renderer.js          # registry経由に変更
├── store.js             # 既存
└── index.js             # Phase 2/3 で軽量化
```

### 1.2 Shape インターフェース

```javascript
// shapes/base.js
/**
 * @typedef {Object} ShapeDefinition
 * @property {string} type - 描画タイプ名 (例: "line", "arrow")
 * @property {string} displayName - 表示名 (例: "直線", "矢印")
 * @property {function} hitTest - ヒットテスト関数
 * @property {function} render - 描画関数
 * @property {function} getHandles - ハンドル位置取得
 * @property {function} moveHandle - ハンドル移動
 * @property {function} moveWhole - 全体移動
 * @property {function} [create] - 初期ジオメトリ作成 (ツール用)
 */
```

### 1.3 例: line.js

```javascript
// shapes/line.js
export const lineShape = {
  type: "line",
  displayName: "直線",

  hitTest(point, drawing, scale) {
    const { start, end } = drawing.geometry;
    return distancePointToSegment(point, start, end) <= withStyleThreshold(scale, drawing);
  },

  render(ctx, drawing, mapper, selected) {
    const [a, b] = projectPoints(mapper, [drawing.geometry.start, drawing.geometry.end]);
    // ... 描画ロジック
  },

  getHandles(drawing) {
    return [
      { id: "start", position: drawing.geometry.start },
      { id: "end", position: drawing.geometry.end },
    ];
  },

  moveHandle(drawing, handleId, point) {
    const geometry = { ...drawing.geometry };
    if (handleId === "start") geometry.start = point;
    if (handleId === "end") geometry.end = point;
    return { geometry };
  },

  moveWhole(drawing, delta) {
    return {
      geometry: {
        ...drawing.geometry,
        start: { x: drawing.geometry.start.x + delta.x, y: drawing.geometry.start.y + delta.y },
        end: { x: drawing.geometry.end.x + delta.x, y: drawing.geometry.end.y + delta.y },
      },
    };
  },

  create(point, options) {
    return {
      type: "line",
      geometry: { start: point, end: point },
      style: { color: options.color },
    };
  },
};
```

### 1.4 Registry 実装

```javascript
// shapes/registry.js
const shapes = new Map();

export const registerShape = (shape) => {
  shapes.set(shape.type, shape);
};

export const getShape = (type) => shapes.get(type);

export const getAllShapes = () => [...shapes.values()];

// 初期登録
import { lineShape } from "./line.js";
import { arrowShape } from "./arrow.js";
// ... 他のシェイプ

[lineShape, arrowShape, /* ... */].forEach(registerShape);
```

### 1.5 renderer.js の変更

```javascript
// Before: 巨大 switch 文
switch (drawing.type) {
  case "line": ...
  case "arrow": ...
}

// After: registry 経由
const shape = getShape(drawing.type);
if (shape?.render) {
  shape.render(ctx, drawing, mapper, selected);
}
```

### 1.6 hittest.js の変更

```javascript
// Before
export const hitTest = (point, drawing, scale) => {
  switch (drawing.type) { ... }
};

// After
export const hitTest = (point, drawing, scale) => {
  const shape = getShape(drawing.type);
  return shape?.hitTest?.(point, drawing, scale) ?? false;
};
```

---

## Phase 2: ツールハンドラー分離 (1日目後半〜2日目前半)

### 2.1 新しいファイル構造

```
resources/js/editor/tools/
├── registry.js          # Tool登録・取得
├── base.js              # 共通インターフェース
├── select.js            # 選択ツール
├── pen.js               # ペンツール
├── line.js              # 直線ツール
├── arrow.js             # 矢印ツール
├── marker.js            # マーカーツール
├── angle.js             # 角度ツール
├── shape.js             # 図形ツール (円)
├── text.js              # テキストツール
└── stamp.js             # スタンプツール
```

### 2.2 Tool インターフェース

```javascript
// tools/base.js
/**
 * @typedef {Object} ToolDefinition
 * @property {string} name - ツール名 (例: "select", "line")
 * @property {string} cursor - カーソルスタイル
 * @property {function} onPointerDown - ポインタダウン処理
 * @property {function} onPointerMove - ポインタムーブ処理
 * @property {function} onPointerUp - ポインタアップ処理
 */
```

### 2.3 例: select.js

```javascript
// tools/select.js
export const selectTool = {
  name: "select",
  cursor: "default",

  onPointerDown(ctx) {
    const { point, store, scale } = ctx;

    // Origin ハンドル判定
    const origin = store.analysis.origin ?? { x: 0, y: 0 };
    if (distance(point, origin) <= toVideoThreshold(scale)) {
      return { action: "drag-origin" };
    }

    // 描画ヒットテスト
    const hit = [...store.analysis.drawings].reverse().find((d) => hitTest(point, d, scale));
    if (hit) {
      store.select(hit.id);
      const handle = findHandle(point, hit, scale);
      return { action: handle ? "drag-handle" : "drag-move", handle, drawingId: hit.id };
    }

    store.select(null);
    return { action: "none" };
  },

  onPointerMove(ctx, state) {
    const { point, store } = ctx;
    if (state.action === "drag-origin") {
      store.setOrigin(point);
    } else if (state.action === "drag-handle") {
      const shape = getShape(store.getDrawing(state.drawingId)?.type);
      shape?.moveHandle?.(/* ... */);
    } else if (state.action === "drag-move") {
      const shape = getShape(store.getDrawing(state.drawingId)?.type);
      shape?.moveWhole?.(/* ... */);
    }
  },

  onPointerUp(ctx, state) {
    return { action: "none" };
  },
};
```

### 2.4 index.js の変更

```javascript
// Before: 300行の handlePointerDown
const handlePointerDown = (e) => {
  switch (tool) {
    case "select": ...
    case "pen": ...
    // 600行のswitch文
  }
};

// After: ツールに委譲
const handlePointerDown = (e) => {
  const tool = getTool(store.currentTool);
  const ctx = { point, store, scale, canvas, video, options };
  toolState = tool.onPointerDown(ctx);
};
```

---

## Phase 3: パン/ズーム対応 (2日目後半)

### 3.1 View Transform の追加

```javascript
// store.js に追加
class EditorStore {
  constructor() {
    // ... 既存
    this.viewTransform = {
      pan: { x: 0, y: 0 },  // CSS px
      zoom: 1,              // 1 = 100%
    };
  }

  setPan(x, y) {
    this.viewTransform.pan = { x, y };
  }

  setZoom(zoom, center) {
    // center を基準にズーム (ピンチズーム対応)
    const oldZoom = this.viewTransform.zoom;
    this.viewTransform.zoom = Math.max(0.1, Math.min(10, zoom));
    // pan 調整でズーム中心を維持
    // ...
  }
}
```

### 3.2 canvas-mapper.js の拡張

```javascript
// buildMapper に viewTransform を組み込み
export const buildMapper = (canvas, video, viewTransform = { pan: { x: 0, y: 0 }, zoom: 1 }) => {
  // ... 既存の計算

  // ビュー変換を適用
  const effectiveScale = rect.scale * viewTransform.zoom;
  const effectiveOffsetX = rect.offsetX + viewTransform.pan.x;
  const effectiveOffsetY = rect.offsetY + viewTransform.pan.y;

  return {
    // ... 既存 + viewTransform 情報
    viewTransform,
    effectiveScale,
    effectiveOffsetX,
    effectiveOffsetY,
  };
};
```

### 3.3 イベントハンドラー追加

```javascript
// index.js に追加
canvas.addEventListener("wheel", (e) => {
  e.preventDefault();
  const delta = e.deltaY > 0 ? 0.9 : 1.1;
  const rect = canvas.getBoundingClientRect();
  const center = {
    x: e.clientX - rect.left,
    y: e.clientY - rect.top,
  };
  store.setZoom(store.viewTransform.zoom * delta, center);
  markDirty();
}, { passive: false });

// 中ボタンドラッグでパン
let isPanning = false;
let panStart = null;

canvas.addEventListener("pointerdown", (e) => {
  if (e.button === 1) { // Middle button
    isPanning = true;
    panStart = { x: e.clientX, y: e.clientY };
    e.preventDefault();
  }
});

canvas.addEventListener("pointermove", (e) => {
  if (isPanning && panStart) {
    const dx = e.clientX - panStart.x;
    const dy = e.clientY - panStart.y;
    store.setPan(
      store.viewTransform.pan.x + dx,
      store.viewTransform.pan.y + dy
    );
    panStart = { x: e.clientX, y: e.clientY };
    markDirty();
  }
});
```

### 3.4 UI: ズームコントロール

```html
<!-- Blade に追加 -->
<div class="zoom-controls">
  <button id="zoom-out">-</button>
  <span id="zoom-level">100%</span>
  <button id="zoom-in">+</button>
  <button id="zoom-fit">Fit</button>
</div>
```

---

## Phase 4: index.js 軽量化 (2日目)

### 4.1 目標

- **Before**: 1,176行
- **After**: ~400行 (イベント登録 + UI更新のみ)

### 4.2 分離する責務

| 責務 | 移動先 |
|------|--------|
| 描画ロジック | shapes/*.js |
| ツールロジック | tools/*.js |
| ヒットテスト | shapes/*.js (registry経由) |
| スタイル適用 | renderer.js (既存) |
| 座標変換 | canvas-mapper.js (既存) |

### 4.3 残す責務

- DOM要素取得・イベントリスナー登録
- Store初期化
- UIパネル更新 (keyframes, drawings list, style panel)
- API通信 (save/load)

---

## 実装順序

### Day 1 (午前)
1. `shapes/` ディレクトリ作成
2. `shapes/registry.js` 実装
3. `shapes/line.js`, `shapes/arrow.js` 実装 (最も複雑なバリエーション)
4. `renderer.js` を registry 経由に変更
5. テスト: 既存の描画が壊れていないか確認

### Day 1 (午後)
6. 残りの shapes を実装 (pen, marker, angle, circle, text, stamp, track, stopwatch)
7. `hittest.js` を registry 経由に変更
8. テスト: 選択・編集が動作するか確認

### Day 2 (午前)
9. `tools/` ディレクトリ作成
10. `tools/registry.js`, `tools/select.js` 実装
11. 描画ツール実装 (line, arrow, pen, marker, angle, shape, text, stamp)
12. `index.js` からツールロジックを削除

### Day 2 (午後)
13. パン/ズーム: `store.js` に viewTransform 追加
14. `canvas-mapper.js` 拡張
15. ホイールズーム、中ボタンパン実装
16. ズームコントロール UI 追加
17. 全体テスト

---

## リスク・注意点

1. **段階的移行**: 一度にすべて変更せず、各 Phase で動作確認
2. **座標系**: パン/ズーム追加時、ヒットテストの座標変換を忘れずに
3. **Undo/Redo**: viewTransform は Undo 対象外にする（描画データのみ）
4. **パフォーマンス**: ズーム時の再描画頻度に注意

---

## 成果物

- [ ] shapes/*.js (11ファイル)
- [ ] tools/*.js (9ファイル)
- [ ] canvas-mapper.js 拡張
- [ ] store.js 拡張
- [ ] index.js 軽量化 (~400行)
- [ ] ズームコントロール UI
- [ ] テスト追加
