const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const distance = (p1, p2) => Math.hypot(p1.x - p2.x, p1.y - p2.y);

const distancePointToSegment = (p, a, b) => {
  const ab = { x: b.x - a.x, y: b.y - a.y };
  const ap = { x: p.x - a.x, y: p.y - a.y };
  const ab2 = ab.x * ab.x + ab.y * ab.y;
  if (ab2 === 0) return distance(p, a);
  const t = clamp((ap.x * ab.x + ap.y * ab.y) / ab2, 0, 1);
  const proj = { x: a.x + ab.x * t, y: a.y + ab.y * t };
  return distance(p, proj);
};

const angleRad = (a, b, c) => {
  const ab = { x: a.x - b.x, y: a.y - b.y };
  const cb = { x: c.x - b.x, y: c.y - b.y };
  const dot = ab.x * cb.x + ab.y * cb.y;
  const mag = Math.sqrt(ab.x * ab.x + ab.y * ab.y) * Math.sqrt(cb.x * cb.x + cb.y * cb.y);
  if (mag === 0) return 0;
  const cos = clamp(dot / mag, -1, 1);
  return Math.acos(cos);
};

const angleDeg = (a, b, c) => (angleRad(a, b, c) * 180) / Math.PI;

const arrowHead = (start, end, size = 12) => {
  const dx = end.x - start.x;
  const dy = end.y - start.y;
  const len = Math.hypot(dx, dy) || 1;
  const ux = dx / len;
  const uy = dy / len;
  const left = {
    x: end.x - ux * size + uy * (size * 0.6),
    y: end.y - uy * size - ux * (size * 0.6),
  };
  const right = {
    x: end.x - ux * size - uy * (size * 0.6),
    y: end.y - uy * size + ux * (size * 0.6),
  };
  return { left, right };
};

export { clamp, distance, distancePointToSegment, angleRad, angleDeg, arrowHead };
