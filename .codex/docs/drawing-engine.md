# Drawing Engine Spec (Video Coach)

## Goals
- Persist coordinates in VIDEO pixel space only.
- Deterministic redraw: clear -> draw all.
- Same mapping for draw / hit-test / edit handles.
- Retina safe (devicePixelRatio).

## Coordinate spaces
- Video space: (vx, vy) in [0..videoWidth], [0..videoHeight]
- Canvas backing store: (cx, cy) in [0..canvasWidthPx], [0..canvasHeightPx]
- Canvas CSS space: layout pixels (clientWidth/clientHeight)

## Layout assumptions
- video element uses `object-fit: contain`
- letterboxing may exist; compute displayed video rect inside canvas.

## Core functions (must be pure)
### computeVideoRectInCanvas(canvasCssW, canvasCssH, videoW, videoH)
Returns:
- drawW, drawH (CSS px)
- offsetX, offsetY (CSS px)
- scale (CSS px per video px)  // uniform

Formula:
- scale = min(canvasCssW/videoW, canvasCssH/videoH)
- drawW = videoW * scale
- drawH = videoH * scale
- offsetX = (canvasCssW - drawW)/2
- offsetY = (canvasCssH - drawH)/2

### videoToCanvas(vx, vy, rect, dpr)
- cx = (offsetX + vx*scale) * dpr
- cy = (offsetY + vy*scale) * dpr

### canvasToVideo(cx, cy, rect, dpr)
- xCss = cx / dpr
- yCss = cy / dpr
- vx = (xCss - offsetX) / scale
- vy = (yCss - offsetY) / scale

Clamp:
- vx in [0..videoW], vy in [0..videoH]

## Rendering rules
- ctx.save/restore per annotation
- style: color, lineWidth, opacity; lineWidth scales with zoom? (MVP: in screen px)
- drawing order: by createdAt ascending (or stable id order)

## Hit-test rules (MVP)
- point distance threshold = max(6px, lineWidth*2) in screen px
- For line/arrow: distance point-to-segment in screen px
- For angle: selectable by nearest segment or vertex proximity

## Tool data model (analysis.drawings[])
- pen: geometry.path[] in video px
- line/arrow: geometry.start/end in video px
- marker: geometry.position in video px
- text: geometry.position + content + fontSize
- angle: geometry.points[3] in video px + computed angleDeg
