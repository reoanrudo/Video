# Editor Interactions Spec

## 1. Interaction Principles
- UIはシンプルに保ち、操作体系は「ツール選択 → クリック/ドラッグ → 編集 → 保存」を基本とする。
- 右クリック操作が前提の機能は、Webでは以下を優先:
  1) 右パネル（Properties）で変更
  2) 可能なら簡易ドロップダウンやコンテキストメニュー
- すべての注釈は「動画ピクセル座標」で保存し、表示時のみ変換する。
- サブツール（④/⑦/⑧/⑩）は Kinovea の長押しを Web のドロップダウン選択で代替する。

---

## 2. Tool Model（Kinovea ①②③④⑤⑦⑧⑨⑩⑪⑬⑭⑮ 採用）
### Common
- tool = ①移動 | ②キーイメージ | ③コメント | ④テキスト/オートナンバー | ⑤ペン | ⑦ライン | ⑧矢印 | ⑨クロスマーカー | ⑩角度 | ⑪ストップウォッチ | ⑬トラック簡易 | ⑭図形 | ⑮テンプレ/スタンプ（MVPは最小限）
- pointer events: pointerdown / pointermove / pointerup（mouse/touch統一）
- redrawは requestAnimationFrame でまとめる
- 座標は動画px、mapper（contain+letterbox+dpr）を描画/ヒットテスト/ハンドルで共用

### ① 移動
- pointerdown: ヒットテストで最上位を選択、ハンドル判定。
- pointermove: ドラッグで移動（ハンドル/全体）。
- pointerup: 変更確定、Undoに積む。

### ② キーイメージ
- クリックで現在時刻を追加。
- 自動追加: 注釈確定時に±0.3s以内に重複がなければ生成。
- サムネクリックでジャンプ。

### ③ コメント
- 停止中のシーンにメモを記録（動画上には表示しないメタ情報）。

### ④ テキスト / オートナンバー（ドロップダウン）
- テキスト: クリック→配置。プロパティで色/サイズ/内容編集。
- オートナンバー: 連番を付与（Phase 2で実装可）。

### ⑤ ペン
- フリーハンド（動画pxでパス保持）。MVPは全体移動のみ、点編集は後続。

### ⑦ ライン / Polyline / 円（ドロップダウン）
- MVP: 直線。長さはpx表示まで（実寸は較正が必要なので後続）。
- Polyline/円はPhase 2+で拡張。

### ⑧ 矢印（ドロップダウン）
- MVP: 通常矢印 + 破線矢印。その他（曲線/波線/ポリライン系）はPhase 2。

### ⑨ クロスマーカー
- 点を配置。座標表示。原点移動（軸ドラッグ）をWeb向けUIで代替。
- 追尾（トラッキング）はMVP外。

### ⑩ 角度系（ドロップダウン）
- MVP: 3点角度。Goniometer/水平角/垂直角はPhase 2。

### ⑪ ストップウォッチ
- 開始点クリック→終了点クリックで経過時間。右クリック開始/停止はWebで置換。

### ⑬ トラック簡易
- 位置記録の最低限（追尾なし）。MVPで余力あれば ID 確保。

### ⑭ 図形
- 矩形/楕円など。MVPは円相当を優先（⑦円と重複可）。

### ⑮ テンプレ/スタンプ
- MVPは最小限（例: 汎用マーク）。詳細はPhase 2で拡張。

---

## 3. Selection & Handles
### Handles (MVP)
- line/arrow: start/end の2ハンドル
- angle: 3ハンドル（A/O/B）
- marker/text: 1ハンドル（position）
- pen: 1ハンドル（全体移動のみ）

### Hit-test (Screen-based threshold)
- 閾値は screen px（例: max(6px, lineWidth*2)）で安定させる
- line/arrow: 点-線分距離
- angle: 3点近傍 or セグメント近傍
- pen: 近傍点/セグメントの簡易判定（MVPは緩めで良い）

---

## 4. Properties Panel (Right)
選択注釈に対して以下を編集可能にする（MVP）
- color
- lineWidth
- opacity
- text: fontSize（任意）、content（任意）

UIルール:
- 値変更は即時反映（redraw）
- 変更確定はhistoryに積む（連続操作はdebounce可）

---

## 5. Key Images (Keyframes)
### Manual add
- ボタンで「現在時刻のサムネ」を追加
- title/comment は任意

### Auto add (Important)
- 注釈確定時（①以外の描画ツール）に自動でキーイメージを追加する
- 重複抑止:
  - 同一動画・同一時刻近傍（例: ±0.3s）に既存キーイメージがあれば追加しない
- 自動追加はユーザーが編集可能（タイトル/メモ）

### Jump
- サムネクリックで該当時刻へシーク
- 2画面の場合は「どちらの動画のキーイメージか」を保持する

---

## 6. Keyboard Shortcuts (Recommended)
- Ctrl/Cmd + Z: Undo
- Ctrl/Cmd + Shift + Z: Redo
- Delete/Backspace: 選択注釈の削除
- Space: 再生/停止（可能なら）

---

## 7. Save/Load
- Editor起動時:
  - GET /analysis → state復元 → redraw
- 保存:
  - PUT /analysis
  - 成功通知はトースト程度（アラート連発は避ける）

---

## 8. Notes on Dual Screen Sync (Optional)
- 各画面に同期点ボタン
- 同期点が双方に揃ったら同期ON
- 同期中は片方のtimeupdateで他方を補正（許容誤差閾値を設定）
