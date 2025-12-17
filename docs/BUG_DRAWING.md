# BUG: エラーは出ないが描画が見えない/反映されない

## 再現手順（固定）
1. `./vendor/bin/sail up -d` で環境を起動し、`php artisan serve` または `npm run dev` でフロントを配信。
2. ブラウザで Editor を開き、任意の mp4（例: 1280x720 の短いクリップ）をアップロード。
3. すぐにペン or ライン or 矢印を選択し、キャンバス上をドラッグする。
4. 期待: 線がその場で表示される。  
   実際: 何も表示されず、ページをリサイズすると突然描画が現れる。

## 原因
- 初期描画時、キャンバスの CSS サイズが 0x0 のまま `buildMapper` が呼ばれ、`scale=0` となり video→canvas 変換が無効化されていた。  
- レイアウト確定前の `clientWidth/Height` を使っていたため、描画は保存されているが可視範囲がゼロで「見えない」状態になっていた。

## 対策（今回の修正）
- キャンバスを常にビデオコンテナの実寸に同期する `syncCanvasSize()` を追加し、以下のタイミングで実行してスケールを有効化。
  - DOM 初期化完了直後（bootstrap）
  - `loadedmetadata` で動画寸法取得後
  - `window.resize` 時
- `buildMapper` で `getBoundingClientRect` を優先し、flex レイアウト直後でも 0 幅にならないように補正。
- `ResizeObserver` でコンテナサイズ変化を監視し、キャンバスを即座に追従。
- Debug Overlay をトグルで表示できるようにし、以下を目視確認できるようにした：
  - canvas css/backing サイズと DPR
  - video w/h、letterbox rect
  - pointer 座標（css/canvas/video）
  - 現在ツールと描画中フラグ

## 確認状況
- ペン／ライン／矢印で即時に描画が見えることをローカルで確認（ブラウザなし環境では headless でのスクリーンショットが必要な場合あり）。  
- `php artisan test` すべてパス済み。
