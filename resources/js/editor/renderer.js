import { buildMapper, videoToCanvas } from "./canvas-mapper";
import {
  angleDeg,
  angleToHorizontal,
  angleToVertical,
  arrowHead,
  arrowHeadVectors,
  distance,
  sampleQuadraticBezier,
  samplePolyline,
  squigglyPoints,
} from "./geometry";

const schedule = (fn) => {
  let rafId = null;
  return (...args) => {
    if (rafId) cancelAnimationFrame(rafId);
    rafId = requestAnimationFrame(() => fn(...args));
  };
};

/**
 * Kinovea参照: DrawingCrossMark.cs
 * 十字マーカーを描画。クロス形状（+）で中心点をマーク。
 * @param {CanvasRenderingContext2D} ctx
 * @param {{x: number, y: number}} p - canvas座標
 * @param {number} dpr
 * @param {string} color
 * @param {number} lineWidth
 */
const drawCrossMarker = (ctx, p, dpr, color, lineWidth = 2) => {
  const armLength = 10 * dpr;
  const lw = lineWidth * dpr;

  ctx.save();
  ctx.strokeStyle = color;
  ctx.lineWidth = lw;
  ctx.lineCap = "round";

  // 十字の横線
  ctx.beginPath();
  ctx.moveTo(p.x - armLength, p.y);
  ctx.lineTo(p.x + armLength, p.y);
  ctx.stroke();

  // 十字の縦線
  ctx.beginPath();
  ctx.moveTo(p.x, p.y - armLength);
  ctx.lineTo(p.x, p.y + armLength);
  ctx.stroke();

  // 中心の白い輪郭（視認性向上）
  ctx.strokeStyle = "white";
  ctx.lineWidth = lw + 2 * dpr;
  ctx.globalCompositeOperation = "destination-over";
  ctx.beginPath();
  ctx.moveTo(p.x - armLength, p.y);
  ctx.lineTo(p.x + armLength, p.y);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(p.x, p.y - armLength);
  ctx.lineTo(p.x, p.y + armLength);
  ctx.stroke();

  ctx.restore();
};

/**
 * Kinovea参照: ArrowHelper.cs
 * 矢印を描画。ペン幅ベースでサイズ調整。
 * - 矢印の底辺: 1 * penWidth (セグメント内側)
 * - 矢印の先端: 3 * penWidth (セグメント外側)
 * - 側面: 1.5 * penWidth (垂直方向)
 */
const drawArrowLine = (ctx, a, b, lineWidth, dpr, fillArrow = true) => {
  const lw = lineWidth * dpr;
  const arrowBase = 1 * lw;
  const arrowTip = 3 * lw;
  const arrowSide = 1.5 * lw;

  // ベクトル計算
  const dx = b.x - a.x;
  const dy = b.y - a.y;
  const len = Math.sqrt(dx * dx + dy * dy);
  if (len === 0) return;

  const ux = dx / len;
  const uy = dy / len;
  // 垂直ベクトル
  const vx = -uy;
  const vy = ux;

  // 線分の終点を矢印の底辺に合わせて短縮
  const lineEndX = b.x - ux * arrowBase;
  const lineEndY = b.y - uy * arrowBase;

  // 線を描画
  ctx.beginPath();
  ctx.moveTo(a.x, a.y);
  ctx.lineTo(lineEndX, lineEndY);
  ctx.stroke();

  // 矢印の頂点
  const tipX = b.x + ux * arrowTip;
  const tipY = b.y + uy * arrowTip;
  // 矢印の左右の点
  const leftX = b.x - ux * arrowBase + vx * arrowSide;
  const leftY = b.y - uy * arrowBase + vy * arrowSide;
  const rightX = b.x - ux * arrowBase - vx * arrowSide;
  const rightY = b.y - uy * arrowBase - vy * arrowSide;

  // 矢印を描画
  ctx.beginPath();
  ctx.moveTo(tipX, tipY);
  ctx.lineTo(leftX, leftY);
  ctx.lineTo(rightX, rightY);
  ctx.closePath();
  if (fillArrow) {
    ctx.fill();
  } else {
    ctx.stroke();
  }
};

const drawLine = (ctx, a, b, withArrow = false, lineWidth = 2, dpr = 1) => {
  if (withArrow) {
    drawArrowLine(ctx, a, b, lineWidth, dpr);
  } else {
    ctx.beginPath();
    ctx.moveTo(a.x, a.y);
    ctx.lineTo(b.x, b.y);
    ctx.stroke();
  }
};

const roundedRectPath = (ctx, x, y, width, height, radius) => {
  const r = Math.min(radius, width / 2, height / 2);
  ctx.beginPath();
  ctx.moveTo(x + r, y);
  ctx.lineTo(x + width - r, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + r);
  ctx.lineTo(x + width, y + height - r);
  ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
  ctx.lineTo(x + r, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - r);
  ctx.lineTo(x, y + r);
  ctx.quadraticCurveTo(x, y, x + r, y);
};

const drawTag = (ctx, text, position, dpr) => {
  ctx.save();
  ctx.font = `${12 * dpr}px system-ui`;
  const padding = 6 * dpr;
  const metrics = ctx.measureText(text);
  const width = metrics.width + padding * 2;
  const height = 22 * dpr;
  const x = position.x + 8 * dpr;
  const y = position.y - height - 4 * dpr;

  ctx.fillStyle = "rgba(255,255,255,0.92)";
  ctx.strokeStyle = "#e2e8f0";
  ctx.lineWidth = 1 * dpr;
  if (typeof ctx.roundRect === "function") {
    ctx.beginPath();
    ctx.roundRect(x, y, width, height, 6 * dpr);
  } else {
    roundedRectPath(ctx, x, y, width, height, 6 * dpr);
  }
  ctx.fill();
  ctx.stroke();

  ctx.fillStyle = "#0f172a";
  ctx.textBaseline = "middle";
  ctx.fillText(text, x + padding, y + height / 2);
  ctx.restore();
};

/**
 * Kinovea参照: AngleHelper.cs
 * 角度を描画。3点（A, O, B）で定義。
 * - O が頂点
 * - A と B が脚の端点
 * - 円弧と角度ラベルを表示
 */
const drawAngle = (ctx, a, o, b, dpr, color, lineWidth = 2) => {
  const lw = lineWidth * dpr;
  const arcRadius = 30 * dpr;

  ctx.save();
  ctx.strokeStyle = color;
  ctx.fillStyle = color;
  ctx.lineWidth = lw;
  ctx.lineCap = "round";
  ctx.lineJoin = "round";

  // 脚を描画
  ctx.beginPath();
  ctx.moveTo(a.x, a.y);
  ctx.lineTo(o.x, o.y);
  ctx.lineTo(b.x, b.y);
  ctx.stroke();

  // 頂点のマーカー
  ctx.beginPath();
  ctx.arc(o.x, o.y, 4 * dpr, 0, Math.PI * 2);
  ctx.fill();

  // 角度計算
  const angleA = Math.atan2(a.y - o.y, a.x - o.x);
  const angleB = Math.atan2(b.y - o.y, b.x - o.x);

  // 円弧描画（反時計回りで小さい角度側）
  let startAngle = angleA;
  let endAngle = angleB;
  let sweep = endAngle - startAngle;
  if (sweep > Math.PI) sweep -= 2 * Math.PI;
  if (sweep < -Math.PI) sweep += 2 * Math.PI;

  const counterClockwise = sweep < 0;
  ctx.globalAlpha = 0.3;
  ctx.beginPath();
  ctx.moveTo(o.x, o.y);
  ctx.arc(o.x, o.y, arcRadius, startAngle, endAngle, counterClockwise);
  ctx.closePath();
  ctx.fill();

  ctx.globalAlpha = 1;
  ctx.beginPath();
  ctx.arc(o.x, o.y, arcRadius, startAngle, endAngle, counterClockwise);
  ctx.stroke();

  // 角度ラベル
  const deg = angleDeg(a, o, b).toFixed(1);
  const midAngle = startAngle + sweep / 2;
  const labelRadius = arcRadius + 16 * dpr;
  const labelX = o.x + Math.cos(midAngle) * labelRadius;
  const labelY = o.y + Math.sin(midAngle) * labelRadius;

  ctx.fillStyle = "#0f172a";
  ctx.font = `bold ${13 * dpr}px system-ui`;
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";

  // ラベル背景
  const labelText = `${deg}°`;
  const metrics = ctx.measureText(labelText);
  const padding = 4 * dpr;
  ctx.fillStyle = "rgba(255, 255, 255, 0.9)";
  ctx.fillRect(
    labelX - metrics.width / 2 - padding,
    labelY - 7 * dpr - padding,
    metrics.width + padding * 2,
    14 * dpr + padding * 2
  );

  ctx.fillStyle = "#0f172a";
  ctx.fillText(labelText, labelX, labelY);

  ctx.restore();
};

const drawHandles = (ctx, points, dpr) => {
  ctx.save();
  ctx.globalAlpha = 1;
  ctx.fillStyle = "#ffffff";
  ctx.strokeStyle = "#3b82f6";
  ctx.lineWidth = 2 * dpr;
  const size = 10 * dpr;
  const half = size / 2;
  points.forEach((p) => {
    ctx.beginPath();
    ctx.rect(p.x - half, p.y - half, size, size);
    ctx.fill();
    ctx.stroke();
  });
  ctx.restore();
};

const projectPoint = (mapper, point) => {
  const { cx, cy } = videoToCanvas(point.x, point.y, mapper.rect, mapper.dpr);
  return { x: cx, y: cy };
};

const projectPoints = (mapper, points) => points.map((p) => projectPoint(mapper, p));

const applyStyle = (ctx, drawing, dpr) => {
  const color = drawing.style?.color ?? "#3b82f6";
  const opacity = drawing.style?.opacity ?? 1;
  const lineWidth = drawing.style?.lineWidth ?? 2;
  const dashLength = drawing.style?.dashLength ?? 8;
  const dashGap = drawing.style?.dashGap ?? 8;
  const dash = drawing.style?.dash ?? false;

  ctx.strokeStyle = color;
  ctx.fillStyle = color;
  ctx.globalAlpha = opacity;
  ctx.lineWidth = lineWidth * dpr;
  ctx.lineCap = "round";
  ctx.lineJoin = "round";
  ctx.setLineDash(dash ? [dashLength * dpr, dashGap * dpr] : []);
};

const drawArrowPolyline = (ctx, points, lineWidth, dpr) => {
  if (points.length < 2) return;
  ctx.beginPath();
  ctx.moveTo(points[0].x, points[0].y);
  for (let i = 1; i < points.length; i++) ctx.lineTo(points[i].x, points[i].y);
  ctx.stroke();
  const tail = points[points.length - 2];
  const head = points[points.length - 1];
  const { left, right } = arrowHead(tail, head, 8 * lineWidth * dpr);
  ctx.beginPath();
  ctx.moveTo(head.x, head.y);
  ctx.lineTo(left.x, left.y);
  ctx.lineTo(right.x, right.y);
  ctx.closePath();
  ctx.fill();
};

const deriveCurveControl = (start, end, curvature = 0.3) => {
  const mid = { x: (start.x + end.x) / 2, y: (start.y + end.y) / 2 };
  const dx = end.x - start.x;
  const dy = end.y - start.y;
  const len = Math.hypot(dx, dy) || 1;
  const nx = -dy / len;
  const ny = dx / len;
  const offset = len * curvature;
  return { x: mid.x + nx * offset, y: mid.y + ny * offset };
};

const Renderer = (canvas, video, store, getSelection, getDebugState) => {
  const ctx = canvas.getContext("2d");
  let lastMapper = null;

  const render = () => {
    if (!ctx) return;
    const mapper = buildMapper(canvas, video);
    lastMapper = mapper;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw axes if origin is set
    const origin = store.analysis.origin ?? { x: 0, y: 0 };
    const originProjected = projectPoint(mapper, origin);
    ctx.save();
    ctx.strokeStyle = "#cbd5e1";
    ctx.lineWidth = 1 * mapper.dpr;
    ctx.setLineDash([6 * mapper.dpr, 6 * mapper.dpr]);
    ctx.beginPath();
    ctx.moveTo(originProjected.x, 0);
    ctx.lineTo(originProjected.x, canvas.height);
    ctx.moveTo(0, originProjected.y);
    ctx.lineTo(canvas.width, originProjected.y);
    ctx.stroke();
    ctx.restore();

    store.analysis.drawings.forEach((drawing) => {
      const selected = getSelection() === drawing.id;
      ctx.save();
      applyStyle(ctx, drawing, mapper.dpr);
      const lineWidth = drawing.style?.lineWidth ?? 2;

      switch (drawing.type) {
        case "pen": {
          const path = projectPoints(mapper, drawing.geometry.path || []);
          if (path.length > 1) {
            ctx.beginPath();
            ctx.moveTo(path[0].x, path[0].y);
            for (let i = 1; i < path.length; i++) {
              ctx.lineTo(path[i].x, path[i].y);
            }
            ctx.stroke();
          }
          if (selected) drawHandles(ctx, [path[0] ?? { x: 0, y: 0 }, path.at(-1) ?? { x: 0, y: 0 }], mapper.dpr);
          break;
        }
        case "line": {
          const [a, b] = projectPoints(mapper, [drawing.geometry.start, drawing.geometry.end]);
          if (drawing.style?.dash) ctx.setLineDash([8 * mapper.dpr, 8 * mapper.dpr]);
          drawLine(ctx, a, b, false, lineWidth, mapper.dpr);
          const mid = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
          const lenPx = Math.round(distance(drawing.geometry.start, drawing.geometry.end));
          drawTag(ctx, `${lenPx} px`, mid, mapper.dpr);
          if (selected) drawHandles(ctx, [a, b], mapper.dpr);
          break;
        }
        case "arrow": {
          const variant = drawing.variant ?? "normal";
          const style = drawing.style ?? {};
          const lw = drawing.style?.lineWidth ?? 2;
          const startVid = drawing.geometry.start;
          const endVid = drawing.geometry.end;
          const dashArray = style.dash ? [style.dashLength * mapper.dpr, style.dashGap * mapper.dpr] : [];
          let canvasPoints = [];

          if (variant === "curve") {
            const controlVid = drawing.geometry.control ?? deriveCurveControl(startVid, endVid, style.curvature ?? 0.3);
            const sampledVid = sampleQuadraticBezier(startVid, controlVid, endVid, 6);
            canvasPoints = projectPoints(mapper, sampledVid);
            ctx.setLineDash(dashArray);
            drawArrowPolyline(ctx, canvasPoints, lw, mapper.dpr);
            if (selected) drawHandles(ctx, projectPoints(mapper, [startVid, controlVid, endVid]), mapper.dpr);
          } else if (variant.startsWith("polyline")) {
            const ptsVid = drawing.geometry.points ?? [startVid, endVid];
            let sampledVid = [];
            if (variant === "polyline_squiggly") {
              for (let i = 0; i < ptsVid.length - 1; i++) {
                const seg = squigglyPoints(
                  ptsVid[i],
                  ptsVid[i + 1],
                  style.squiggleWavelength ?? 12,
                  style.squiggleAmplitude ?? 4
                );
                if (i > 0) seg.shift();
                sampledVid.push(...seg);
              }
            } else {
              sampledVid = samplePolyline(ptsVid, 6);
            }
            canvasPoints = projectPoints(mapper, sampledVid);
            const useDash = variant === "polyline_dash";
            if (useDash) ctx.setLineDash(dashArray);
            drawArrowPolyline(ctx, canvasPoints, lw, mapper.dpr);
            if (selected) drawHandles(ctx, projectPoints(mapper, ptsVid), mapper.dpr);
          } else if (variant === "squiggly") {
            const sampledVid = squigglyPoints(
              startVid,
              endVid,
              style.squiggleWavelength ?? 12,
              style.squiggleAmplitude ?? 4
            );
            canvasPoints = projectPoints(mapper, sampledVid);
            ctx.setLineDash([]);
            drawArrowPolyline(ctx, canvasPoints, lw, mapper.dpr);
            if (selected) drawHandles(ctx, projectPoints(mapper, [startVid, endVid]), mapper.dpr);
          } else if (variant === "dash") {
            ctx.setLineDash(dashArray);
            const [a, b] = projectPoints(mapper, [startVid, endVid]);
            drawLine(ctx, a, b, true, lw, mapper.dpr);
            if (selected) drawHandles(ctx, [a, b], mapper.dpr);
          } else {
            ctx.setLineDash([]);
            const [a, b] = projectPoints(mapper, [startVid, endVid]);
            drawLine(ctx, a, b, true, lw, mapper.dpr);
            if (selected) drawHandles(ctx, [a, b], mapper.dpr);
          }

          const lenPx = Math.round(distance(drawing.geometry.start, drawing.geometry.end));
          const midCanvas = canvasPoints.length
            ? canvasPoints[Math.floor(canvasPoints.length / 2)]
            : projectPoint(mapper, {
                x: (drawing.geometry.start.x + drawing.geometry.end.x) / 2,
                y: (drawing.geometry.start.y + drawing.geometry.end.y) / 2,
              });
          drawTag(ctx, `${lenPx} px`, midCanvas, mapper.dpr);
          break;
        }
        case "marker": {
          const [p] = projectPoints(mapper, [drawing.geometry.position]);
          drawCrossMarker(ctx, p, mapper.dpr, drawing.style?.color ?? "#3b82f6", lineWidth);
          const origin = store.analysis.origin ?? { x: 0, y: 0 };
          const dx = Math.round(drawing.geometry.position.x - origin.x);
          const dy = Math.round(drawing.geometry.position.y - origin.y);
          drawTag(ctx, `(${dx}, ${dy})`, p, mapper.dpr);
          if (selected) drawHandles(ctx, [p], mapper.dpr);
          break;
        }
        case "text":
        case "autonumber": {
          const [p] = projectPoints(mapper, [drawing.geometry.position]);
          ctx.save();
          ctx.globalAlpha = drawing.style?.opacity ?? 1;
          const fontSize = (drawing.geometry?.fontSize ?? 14) * mapper.dpr;
          ctx.font = `${fontSize}px system-ui`;

          const label = drawing.type === "autonumber"
            ? drawing.geometry.content ?? "1"
            : drawing.geometry.content || "text";

          // テキスト背景
          const metrics = ctx.measureText(label);
          const padding = 4 * mapper.dpr;
          ctx.fillStyle = "rgba(255, 255, 255, 0.85)";
          ctx.fillRect(
            p.x - padding,
            p.y - fontSize - padding,
            metrics.width + padding * 2,
            fontSize + padding * 2
          );

          ctx.fillStyle = drawing.style?.color ?? "#0f172a";
          ctx.fillText(label, p.x, p.y);
          ctx.restore();
          if (selected) drawHandles(ctx, [p], mapper.dpr);
          break;
        }
        case "angle": {
          const variant = drawing.variant ?? "three_point";
          if (variant === "three_point") {
            const pts = projectPoints(mapper, drawing.geometry.points);
            drawAngle(ctx, pts[0], pts[1], pts[2], mapper.dpr, drawing.style?.color ?? "#3b82f6", lineWidth);
            if (selected) drawHandles(ctx, pts, mapper.dpr);
          } else {
            const ptsVid = drawing.geometry.points ?? [];
            const a = ptsVid[0];
            const b = ptsVid[1];
            if (!a || !b) break;
            const anchor = variant === "to_horizontal" ? { x: a.x + 100, y: a.y } : { x: a.x, y: a.y - 100 };
            const proj = variant === "to_horizontal" ? { x: b.x, y: a.y } : { x: a.x, y: b.y };
            const ptsCanvas = projectPoints(mapper, [b, a, proj]);
            drawAngle(ctx, ptsCanvas[0], ptsCanvas[1], ptsCanvas[2], mapper.dpr, drawing.style?.color ?? "#3b82f6", lineWidth);
            const deg =
              variant === "to_horizontal"
                ? Math.abs(angleToHorizontal(a, b)).toFixed(1)
                : Math.abs(angleToVertical(a, b)).toFixed(1);
            drawTag(ctx, `${deg}°`, ptsCanvas[1], mapper.dpr);
            if (selected) drawHandles(ctx, projectPoints(mapper, [a, b]), mapper.dpr);
          }
          break;
        }
        case "circle": {
          const center = projectPoint(mapper, drawing.geometry.center);
          const radius = (drawing.geometry.radius ?? 0) * mapper.rect.scale * mapper.dpr;
          ctx.beginPath();
          ctx.arc(center.x, center.y, radius, 0, Math.PI * 2);
          ctx.stroke();
          if (selected) drawHandles(ctx, [center, { x: center.x + radius, y: center.y }], mapper.dpr);
          break;
        }
        case "stamp": {
          const [p] = projectPoints(mapper, [drawing.geometry.position]);
          ctx.save();
          const label = drawing.geometry?.name ?? "★";
          ctx.font = `${18 * mapper.dpr}px system-ui`;
          ctx.fillText(label, p.x, p.y);
          ctx.restore();
          if (selected) drawHandles(ctx, [p], mapper.dpr);
          break;
        }
        case "comment":
          // Non-visual
          break;
        case "stopwatch": {
          const label = drawing.geometry?.label ?? "Stopwatch";
          ctx.save();
          ctx.globalAlpha = 0.8;
          ctx.fillStyle = drawing.style?.color ?? "#0f172a";
          ctx.font = `${13 * mapper.dpr}px system-ui`;
          ctx.fillText(label, 10 * mapper.dpr, 18 * mapper.dpr);
          ctx.restore();
          break;
        }
        case "track": {
          const path = projectPoints(mapper, drawing.geometry.path || []);
          if (path.length > 1) {
            ctx.beginPath();
            ctx.moveTo(path[0].x, path[0].y);
            for (let i = 1; i < path.length; i++) {
              ctx.lineTo(path[i].x, path[i].y);
            }
            ctx.stroke();
          }
          if (selected && path.length) drawHandles(ctx, [path[0], path.at(-1)], mapper.dpr);
          break;
        }
      }
      ctx.restore();
    });
    if (getDebugState) {
      const debug = getDebugState();
      if (debug?.enabled) {
        drawDebug(ctx, mapper, debug);
      }
    }
  };

  return schedule(render);
};

const drawDebug = (ctx, mapper, debug) => {
  ctx.save();
  ctx.font = `${11 * mapper.dpr}px ui-monospace, SFMono-Regular, Menlo, monospace`;

  const lines = [];
  lines.push(`tool: ${debug.tool ?? "-"}`);
  lines.push(`drawing: ${debug.isDrawing ? "yes" : "no"}`);
  lines.push(`dpr: ${mapper.dpr.toFixed(2)}`);
  lines.push(
    `canvas css: ${Math.round(mapper.canvasCssWidth)}x${Math.round(mapper.canvasCssHeight)} / backing: ${mapper.canvasWidth}x${mapper.canvasHeight}`
  );
  lines.push(`video: ${mapper.videoWidth}x${mapper.videoHeight}`);
  lines.push(
    `contain rect: off(${mapper.rect.offsetX.toFixed(1)}, ${mapper.rect.offsetY.toFixed(1)}), draw(${mapper.rect.drawWidth.toFixed(1)}x${mapper.rect.drawHeight.toFixed(1)}), scale ${mapper.rect.scale.toFixed(4)}`
  );
  if (debug.pointer) {
    const { css, canvas, video } = debug.pointer;
    lines.push(
      `pointer css: ${css.x.toFixed(1)}, ${css.y.toFixed(1)} | canvas: ${canvas.x.toFixed(1)}, ${canvas.y.toFixed(1)} | video: ${video.x.toFixed(1)}, ${video.y.toFixed(1)}`
    );
  }

  const pad = 8 * mapper.dpr;
  const lineHeight = 14 * mapper.dpr;
  const width =
    Math.max(...lines.map((l) => ctx.measureText(l).width), 200 * mapper.dpr) + pad * 2;
  const height = lineHeight * lines.length + pad * 2;

  ctx.fillStyle = "rgba(15, 23, 42, 0.75)";
  ctx.strokeStyle = "rgba(255, 255, 255, 0.35)";
  ctx.lineWidth = 1 * mapper.dpr;
  ctx.fillRect(pad, pad, width, height);
  ctx.strokeRect(pad, pad, width, height);

  ctx.fillStyle = "#e2e8f0";
  ctx.textBaseline = "top";
  lines.forEach((line, i) => {
    ctx.fillText(line, pad * 2, pad * 2 + i * lineHeight);
  });

  // draw letterbox rect for reference
  ctx.strokeStyle = "#22d3ee";
  ctx.lineWidth = 2 * mapper.dpr;
  ctx.strokeRect(
    mapper.rect.offsetX * mapper.dpr,
    mapper.rect.offsetY * mapper.dpr,
    mapper.rect.drawWidth * mapper.dpr,
    mapper.rect.drawHeight * mapper.dpr
  );
  ctx.restore();
};

export { Renderer, projectPoint, projectPoints, drawDebug };
