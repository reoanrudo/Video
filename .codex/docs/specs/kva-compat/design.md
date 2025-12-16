# Design: KVA compatibility

## Architecture
- app/Support/Kva/
  - KvaImporter.php
  - KvaExporter.php
  - V2/Profile.php（対応範囲を段階拡張）

## Mapping doc
- docs/kva-mapping.md に
  - analysis_json.drawings[] ↔ KVA Drawings nodes
  - analysis_json.keyframes[] ↔ KVA Keyframes

## Round-trip strategy
- import: 既知ノード→正規化、未知ノード→extensions.kvaPassthrough
- export: 既知ノード→生成、passthrough→合流（衝突時は既知を優先）

## Tests
- Golden KVA を読み込み → analysis_json → 再出力
- XMLの差分は「意味差分」を優先（順序差は許容する等）
