# リポジトリ構成メモ（Steering）

## 主要エントリ
- `routes/web.php`：ウェブルート。ダッシュボード/エディタ/分析API を定義。
- `app/Http/Controllers/ProjectController.php`：ダッシュボード表示・プロジェクト作成・エディタ表示。
- `app/Http/Controllers/ProjectAnalysisController.php`：分析データ取得・更新 API。`SaveAnalysisRequest` でスキーマ検証。
- `app/Models/Project.php`：`analysis_json` を配列キャスト。
- `database/migrations/2025_11_26_000000_create_projects_table.php`：projects テーブル定義。

## フロント/ビュー
- `resources/views/dashboard.blade.php`：プロジェクト一覧と作成フォーム。
- `resources/views/editor.blade.php`：動画描画エディタ UI。canvas とツールバー/サイドパネル。
- `resources/js/editor/*`：描画ロジック（renderer, hittest, store, geometry, canvas-mapper, index）。

## ドキュメント
- `docs/BUG_DRAWING.md`：初期描画が見えない不具合の原因と対策。
- `docs/drawing-tools.md`：描画ツールのバリアント、データスキーマ、レンダリング/ヒットテスト仕様。
- `.codex/docs/editor-scope.md`：MVP/Phase2 スコープと受け入れ基準。
- `.codex/docs/editor-interactions.md`：ツール挙動と操作モデル。
- `.codex/docs/drawing-engine.md`：座標変換・レンダリング・ヒットテスト原則。
- `.codex/docs/out-of-scope.md`：MVP外機能リスト。
- `.codex/docs/test-users.md`：ローカル環境のテストユーザー情報。

## 既知の負債・注意点
- 分析スキーマの固定値・移行手順が未整備。
- 動画アップロード保存先や容量制限が未記載（実装確認が必要）。

## TODO（次フェーズで調査/決定）
- デプロイ/インフラ構成の把握（環境変数、ストレージ、CD）。
- ブラウザ対応・パフォーマンス要件。
