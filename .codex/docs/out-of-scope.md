# Out of Scope (MVP)

## Purpose
MVPの成功条件（描画の正確性・保存復元・基本編集）を崩す可能性が高い機能を明確に除外し、段階導入する。

---

## Not in MVP (Phase 2+)
### 1) Human model 系（⑥）
- 人体モデル/Bike fit/Archery/Profile/Genu/Posture 等
- 距離水平成分表示（Distance Horizontal）
理由: モデルUIと計測ロジックが大きく、MVP価値に対し工数が大きい

### 2) Calibration / Distortion / Perspective（⑫）
- パースペクトグリッド、グリッド較正、実寸換算
理由: 較正UIと計測精度の担保が必要で、MVPスコープを超える

### 3) Tracking / Follow
- クロスマーカー等の追尾、軌跡追尾、補間
理由: 画像処理コストが高い。⑨は点＋座標表示＋原点移動のみ

### 4) Advanced Stopwatch / Timeline analytics
- 複数区間、イベント管理、統計出力  
理由: UI/モデルが増える。MVPでは「注釈とキーイメージ」を優先する

### 5) Full KVA Import compatibility
- 未知ノードを完全再現する高度Import
- round-trip完全一致の保証（MVPでは最小範囲のみ）  
理由: 互換範囲の確定に時間がかかる。まずExport最小範囲を安定させる

### 6) Advanced editing for pen path
- ペンの点編集、スムージング高度化、ノード操作  
理由: 編集UIの設計負担が大きい。MVPは全体移動のみ

---

## MVP Focus (What we optimize for)
- 描画の正確性（座標系・変換・再描画）
- 保存→復元の再現性
- 採用13ツール（①②③④⑤⑦⑧⑨⑩⑪⑬⑭⑮）の選択/移動/削除 + Undo
- キーイメージ（手動 + 自動）とジャンプ導線
