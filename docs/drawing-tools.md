# 描画ツール拡張要約（Kinovea PDF対応）

- **矢印ツールのサブバリアント**: Arrow / Dash / Squiggly / Curve / Polyline / Polyline dash / Polyline squiggly を提供。
- **角度ツールの派生**: Angle（3点） / Angle to horizontal（水平基準） / Angle to vertical（垂直基準）。
- **スタイル編集**: lineWidth, color, opacity, dash長・間隔、squiggle振幅・波長、curveのcurvature（制御点オフセット率）を右パネルで編集可能。
- **データスキーマ**: `type` は大分類（arrow/angleなど）、`variant` でサブタイプを保持。座標はすべて動画ピクセル系。  
  - arrow: `variant = normal|dash|squiggly|curve|polyline|polyline_dash|polyline_squiggly`  
  - angle: `variant = three_point|to_horizontal|to_vertical`
- **レンダリング**: dash は `setLineDash`、squiggly はサンプル点列生成、curve は二次ベジエサンプリング、polyline は各点を結び、矢尻は終端セグメント方向で描画。描画は requestAnimationFrame でバッチ。
- **ヒットテスト**: すべて線系はサンプル点列化して点-線分距離判定を共通化。curve は control を含む三点、polyline は各点ハンドルあり。
- **保存/復元**: variant とスタイル・ジオメトリを analysis_json に保存し、再読み込みで再現。
