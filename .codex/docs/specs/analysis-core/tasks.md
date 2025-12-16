# Tasks: Analysis Core（Codex実装用）

> 実装はCodexに委任する。各タスク完了時に必ず `php artisan test` を実行する。

## 1. DB変更
- [ ] 1.1 migration: projects に `analysis_schema_version`, `analysis_json`, `analysis_kva_raw` を追加
- [ ] 1.2 Project作成時に初期analysis_jsonをセット

## 2. API
- [ ] 2.1 routes: GET/PUT `/api/projects/{project}/analysis`
- [ ] 2.2 ProjectAnalysisController を追加（authorize含む）
- [ ] 2.3 SaveAnalysisRequest（FormRequest）追加
- [ ] 2.4 AnalysisMigrator（空実装でも良い）を追加し、GETで必ず通す

## 3. テスト
- [ ] 3.1 Feature: GETは認可と200/403をテスト
- [ ] 3.2 Feature: PUTは422/200をテスト
- [ ] 3.3 Migration: 初期JSONが入ること

## 4. Codexに投げる指示（このtasks.mdを参照）
- Task 1.1〜1.2 → 「migrateが通るまで」
- Task 2.1〜2.4 → 「APIの疎通とバリデーション」
- Task 3.x → 「テスト緑化」

Verification:
- `php artisan migrate`
- `php artisan test`
