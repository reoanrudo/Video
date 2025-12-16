# Requirements: Editor MVP（Volt + Canvas）

## Introduction
Voltで編集画面を提供し、動画再生（HTML5 Video）にCanvasを重ねて注釈を描けるようにする。
保存は analysis-core のPUT APIを使用する。

## Requirements

### R1: 画面レイアウト（Google風シンプルUI）
WHEN ユーザーがエディタを開く  
THE SYSTEM SHALL 左ツールバー / 中央動画 / 右パネル（最小）を表示する。

Acceptance Criteria:
- 余白多め、白基調、薄いボーダー
- 主要ボタンは「保存」「Undo」程度

### R2: 動画再生
WHEN ユーザーが動画を選択し再生する  
THE SYSTEM SHALL 再生/停止/シーク/フレーム送りができる。

Acceptance Criteria:
- currentTime と duration の表示
- 再生速度の変更

### R3: 注釈ツール（MVP）
WHEN ユーザーがツールを選択して操作する  
THE SYSTEM SHALL 次の注釈を作成できる: pen, line, arrow, marker, text, angle

Acceptance Criteria:
- 注釈は video pixel座標として保存（canvas座標保存は禁止）
- Undoで直前の注釈追加が戻る

### R4: 保存/復元
WHEN ユーザーが保存ボタンを押す  
THE SYSTEM SHALL PUT /analysis に現在のanalysis_jsonを送る。  
WHEN 画面を再読込する  
THE SYSTEM SHALL GET /analysis を復元して描画を再現する。

Acceptance Criteria:
- 保存→リロードで一致
