# Tasks: Editor MVP（Codex実装用）

## 1. Voltページ
- [ ] 1.1 editor ルートとVoltページ追加（projectを受け取る）
- [ ] 1.2 ツールバー/動画領域/右パネル（最小）を実装
- [ ] 1.3 Canvas/Video領域を `wire:ignore` で隔離

## 2. JSエディタ基盤
- [ ] 2.1 analysis state store（schema含む）
- [ ] 2.2 GET/PUT /analysis のfetchラッパー
- [ ] 2.3 canvas-mapper（video<->canvas座標変換）
- [ ] 2.4 redrawパイプライン（全注釈再描画）

## 3. ツール（最小）
- [ ] 3.1 pen
- [ ] 3.2 line
- [ ] 3.3 arrow
- [ ] 3.4 marker
- [ ] 3.5 text（入力UIはpromptでも可→後で改善）
- [ ] 3.6 angle（三点＋角度表示）

## 4. Undo/Save
- [ ] 4.1 Undo（注釈追加の戻し）
- [ ] 4.2 Save（PUT /analysis）
- [ ] 4.3 Load時復元（GET /analysis）

## 5. 検証
- [ ] 5.1 E2E手動: 保存→リロードで一致
- [ ] 5.2 lint/buildが通る

Verification:
- `npm run build`
- `php artisan test`
