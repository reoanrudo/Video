# Requirements: Analysis Core（保存モデル/API）

## Introduction
動画分析データ（注釈・キーイメージ・作業範囲・同期点など）を、MariaDB上の `projects.analysis_json` に保存し、GET/PUT APIで読み書きできるようにする。
このJSONを「唯一の真実（single source of truth）」とする。

## Glossary
- Analysis JSON: プロジェクトの分析状態を表す単一JSON
- Schema version: `videocoach.analysis@1.0.0` のようなスキーマ名/版
- KVA: Kinoveaの分析ファイル（.kva、XML） [oai_citation:4‡Kinovea](https://www.kinovea.org/help/en/annotation/annotation_files.html?utm_source=chatgpt.com)

## Requirements

### R1: Analysis JSONの永続化
WHEN ユーザーがプロジェクトを作成する  
THE SYSTEM SHALL `projects.analysis_json` を初期スキーマで作成する。

Acceptance Criteria:
- DBに `analysis_schema_version` と `analysis_json` が存在する
- 新規プロジェクト作成時に、空のキー配列を含む初期JSONが入る

### R2: 読み込みAPI
WHEN クライアントが `GET /api/projects/{project}/analysis` を呼ぶ  
THE SYSTEM SHALL 最新の `analysis_json` を返す。

Acceptance Criteria:
- 認可（owner/権限）を満たさない場合は403
- 返却JSONに `schema`（name/version）が必ず含まれる

### R3: 保存API
WHEN クライアントが `PUT /api/projects/{project}/analysis` を呼ぶ  
THE SYSTEM SHALL バリデーション後に `analysis_json` を更新する。

Acceptance Criteria:
- バリデーションエラーは 422（フィールド別エラー）
- `schema.version` が不明な場合は 422
- 更新後、GETで同一内容が取得できる

### R4: スキーマ移行
WHEN `analysis_schema_version` が旧版である  
THE SYSTEM SHALL サーバ側で最新版へマイグレーションして返す（読込時）または更新時に移行する。

Acceptance Criteria:
- v1→v1.0.0 のようなマイグレーターが追加可能な構造
- 移行結果はテストで担保

### R5: 監査/デバッグ
WHEN 保存処理に失敗する  
THE SYSTEM SHALL ログに project_id と原因を残す（機密情報は除く）。

Acceptance Criteria:
- 例外は握り潰さず、APIとして適切なコードを返す
