# 技術スタック・制約（Steering）

## アプリ/サーバー
- Laravel 12, PHP 8.2。Fortify 認証、有効化済み。
- Livewire Volt を利用したビューコンポーネント。`ProjectController` / `ProjectAnalysisController` が主要 API。
- 分析データは `projects` テーブルに `analysis_json`（JSON cast）として保存。`analysis_schema_version` でバージョン管理。

## フロントエンド
- Vite 7 + Tailwind CSS 4 + ES Modules。
- エディタ UI は `resources/views/editor.blade.php` と `resources/js/editor/*`（canvas レンダラ、ヒットテスト、ストア）で構成。
- 描画ツール仕様・スキーマは `docs/drawing-tools.md` に整理済み。

### 対応ブラウザ（暫定案）
- モダン Evergreen デスクトップ（Chrome/Edge/Firefox/Safari 最新）を正式サポート。
- モバイル/タブレットは要確認。要件が出なければデスクトップ優先で進行。

## ローカル開発/ビルド
- `composer install`, `npm install`, `npm run build` が基本。`composer dev` スクリプトでサーバー/キュー/ログ/Vite を並列起動。
- sail 用 compose 設定あり（`compose.yaml`）。DB はデフォルト SQLite（`database/database.sqlite` を post-create で作成）。

## 既知の技術的論点
- 初期描画が見えない問題は `syncCanvasSize` 等で解消（docs/BUG_DRAWING.md）。キャンバスサイズと DPR 同期が重要な前提。
- 分析スキーマは現状 `videocoach.analysis@1.0.0` 固定。将来のバージョンアップ手順・マイグレーションは未定。
- 描画エンジンの座標変換・ヒットテスト原則は `.codex/docs/drawing-engine.md` を参照。

## 未確定/確認事項
- 本番デプロイ先・CD パイプライン、ストレージ（動画アップロード先: S3 など）や最大ファイルサイズ制約。デプロイ方針決定・実装は本タスク側にアサイン済み。
- モバイル/タブレット対応要否。オフライン/低帯域対策の要否。
- アップロード動画の保存方式（現状ローカルか、一時メモリか未確認）。

## 付録: 開発環境補足
- テストユーザーは `php artisan migrate && php artisan db:seed`（`APP_ENV=local`）で作成可能。パスワードは `Password!12345`（`.codex/docs/test-users.md`）。
- Laravel Boost ガイドライン（`.codex/AGENTS.md`）に従い、既存構造を踏襲し、依存追加は慎重に。
