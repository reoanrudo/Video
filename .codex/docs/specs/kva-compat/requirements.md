# Requirements: KVA compatibility（Kinovea互換）

## Introduction
KinoveaのKVA（XML）と互換性のあるエクスポート/インポートを段階的に実装する。
KinoveaはGPL v2のため、コード移植ではなく、仕様参照とKVA生成（fixture）で互換を担保する。 [oai_citation:6‡GitHub](https://github.com/Kinovea/Kinovea?utm_source=chatgpt.com)

## Requirements

### R1: Export（最小）
WHEN ユーザーがエクスポートを実行する  
THE SYSTEM SHALL analysis_json をKVA（XML）に変換してダウンロードさせる。

Scope (MVP):
- Keyframes（title/comment/position）
- Drawings: marker/cross, line, angle（最低限）

Acceptance Criteria:
- Kinoveaで開いて破綻しない
- fixturesのKVAに近い構造を出力できる

### R2: Import（次段階）
WHEN ユーザーがKVAをアップロードする  
THE SYSTEM SHALL 可能な範囲でanalysis_jsonに変換して保存する。

Acceptance Criteria:
- 未対応ノードは extensions.kvaPassthrough に保持し、再エクスポートで失わない

### R3: Golden fixtures（Kinovea活用）
WHEN fixtureを更新する必要がある  
THE SYSTEM SHALL Kinoveaで最小KVAを生成し `tests/Fixtures/kva/` に保存する。 [oai_citation:7‡Kinovea](https://www.kinovea.org/help/en/annotation/annotation_files.html?utm_source=chatgpt.com)

Acceptance Criteria:
- import→export のround-tripテストが存在する
