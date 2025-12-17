# Editor Scope (MVP / Phase 2+)

## Purpose
本アプリのEditorは「動画分析ノート」として、動画上に注釈（描画・計測・メモ）を重ね、保存→復元で再現できることを目的とする。Kinoveaのワークフロー（2動画比較・同期、描画・キーイメージ中心）を参考にするが、Web実装で難易度が高い機能は段階導入とする。

---

## Non-negotiables (Must)
- 永続化座標は必ず「動画ピクセル座標（video px）」で保存する（Canvas/CSS座標保存は禁止）。
- 描画/ヒットテスト/ハンドル操作は同一の座標変換（contain + letterbox + dpr）を使用する。
- deterministic redraw（全消去→全再描画、順序固定）を守る。
- 保存→リロードで完全再現（位置・形状・スタイル・関連時刻）できること。

---

## MVP In Scope
### A. Playback / UI
- 1画面動画再生（再生/停止、シーク、速度変更、簡易フレーム送り）。
- Google風のシンプルUI（白ベース、薄いボーダー、青アクセント）。
- レイアウト: Header（保存/Undo/Redo）、Left（ツールバー）、Center（video+canvas / wire:ignore / JS主導）、Right（Key Images・スタイル）。

### B. Drawing Tools (Kinovea ①②③④⑤⑦⑧⑨⑩⑪⑬⑭⑮ を採用)
- ① 移動（cursor）: 選択/移動/ハンドル操作。
- ② キーイメージ: 手動追加 + 注釈追加時に自動追加（重複抑止）。
- ③ コメント: 動画上に表示しないメモ（メタ情報）。
- ④ テキスト/オートナンバー: Webではドロップダウン選択でサブツール。
- ⑤ ペン: フリーハンド（MVPは全体移動のみ、点編集は後続）。
- ⑦ ライン/Polyline/円: Polylineと円はPhase 2、MVPは直線（px長表示まで）。
- ⑧ 矢印: 通常 + 破線をMVP、曲線/波線/ポリライン系はPhase 2。
- ⑨ クロスマーカー: 座標表示・原点移動のみ。追尾はMVP外。
- ⑩ 角度: 3点角度をMVP。Goniometer/水平/垂直角はPhase 2。
- ⑪ ストップウォッチ: 開始/終了点クリックで計測（右クリック開始/停止はWebで置換）。
- ⑬ トラック/軌跡（最低限の位置記録のみ、追尾なし）※MVPでは省略可だがID確保。
- ⑭ 図形（矩形/楕円などシンプルな図形）※MVPは円相当のみ優先。
- ⑮ テンプレート/スタンプ系（必要最小限、後続で拡張可）。

### C. Selection / Edit
- ハンドル: line/arrow両端、angle 3点、marker/textアンカー、penは全体移動。
- Deleteボタン/キー、Undo/Redo。

### D. Key Images
- 手動追加 + 注釈確定時に自動追加（±0.3s以内は重複抑止）。
- サムネクリックで該当時刻へジャンプ。

### E. Save/Load
- GETで復元、PUTで保存。analysis_json を唯一の真実。

### F. Dual Screen Sync（オプション）
- 2動画並列＋同期ポイント指定→同期再生。

---

## Phase 2+ (Planned / Later)
- ⑧の曲線/波線/ポリライン矢印、⑦ Polyline/円の本格対応。
- ⑩のGoniometer/水平/垂直角。
- ⑬/⑭/⑮の高度化（スタンプ/テンプレート、複合図形）。
- Overlay（重ね表示）モード、実寸キャリブレーション。
- トラッキング（追尾）、KVA Import高度化、ペン点編集、ガイド/レイヤー。

---

## Explicitly Out of Scope for MVP
詳細は `out-of-scope.md` を参照。

---

## Acceptance Criteria (MVP)
- 採用13ツール（①②③④⑤⑦⑧⑨⑩⑪⑬⑭⑮）で作成→選択/移動→削除→Undo/Redo→保存→リロード再現。
- Key Images: 手動追加 + 自動追加（重複抑止）+ サムネクリックでジャンプ。
- 座標は動画ピクセルのみ。mapper共通利用、deterministic redraw。
- `php artisan test` 緑（必要に応じて `npm run build`）。
