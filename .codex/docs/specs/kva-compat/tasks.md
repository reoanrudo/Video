# Tasks: KVA compatibility（Codex実装用）

## 1. Export（最小）
- [ ] 1.1 ExportController（または専用Controller）でKVAダウンロード
- [ ] 1.2 KvaExporter（Keyframes + marker/line/angle）
- [ ] 1.3 docs/kva-mapping.md を作成（対応表）

## 2. Fixtures（Kinoveaを“最大限活用”）
- [ ] 2.1 Kinoveaで最小KVA（marker/line/angle）を生成し tests/Fixtures/kva/ に追加
- [ ] 2.2 golden test：fixture import→export（Exportのみなら exportのスナップショットでも可）

## 3. Import（次）
- [ ] 3.1 KvaImporter（既知ノードのみ）
- [ ] 3.2 未知ノード passthrough 保存
- [ ] 3.3 import→export round-trip の破壊がないことをテスト

Verification:
- `php artisan test`
