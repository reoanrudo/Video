# KVA Mapping (Video Coach)

## Principles
- Known nodes map to analysis_json.
- Unknown nodes/attrs preserved in analysis.extensions.kvaPassthrough.
- Round-trip safety: import -> export should not lose data.

## Supported (MVP)
- Keyframes
- Drawings: marker/cross, line, angle (later: arrow, text, grid, stopwatch...)

## Mapping table
| Internal (analysis_json) | KVA XML Node | Notes |
|---|---|---|
| keyframes[].position.timeSec | Keyframe/Position | ms単位に変換など |
| drawings[type=marker] | DrawingCross (or equivalent) | 位置の表現 |
| drawings[type=line] | DrawingLine | start/end |
| drawings[type=angle] | DrawingAngle | 3 points + angle |
