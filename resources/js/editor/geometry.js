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

const arrowHeadVectors = (start, end, size = 12) => {
  const dx = end.x - start.x;
  const dy = end.y - start.y;
  const len = Math.hypot(dx, dy) || 1;
  const ux = dx / len;
  const uy = dy / len;
  const left = { x: -ux * size + uy * (size * 0.6), y: -uy * size - ux * (size * 0.6) };
  const right = { x: -ux * size - uy * (size * 0.6), y: -uy * size + ux * (size * 0.6) };
  return { left, right, tip: { x: ux * size * 3, y: uy * size * 3 } };
};

const sampleQuadraticBezier = (start, control, end, segmentLength = 6) => {
  const approxLen = distance(start, control) + distance(control, end);
  const steps = Math.max(2, Math.ceil(approxLen / segmentLength));
  const pts = [];
  for (let i = 0; i <= steps; i++) {
    const t = i / steps;
    const mt = 1 - t;
    const x = mt * mt * start.x + 2 * mt * t * control.x + t * t * end.x;
    const y = mt * mt * start.y + 2 * mt * t * control.y + t * t * end.y;
    pts.push({ x, y });
  }
  return pts;
};

const squigglyPoints = (start, end, wavelength = 12, amplitude = 4) => {
  const dx = end.x - start.x;
  const dy = end.y - start.y;
  const len = Math.hypot(dx, dy) || 1;
  const ux = dx / len;
  const uy = dy / len;
  const vx = -uy;
  const vy = ux;

  const step = Math.max(4, wavelength / 2);
  const steps = Math.max(2, Math.ceil(len / step));
  const pts = [];
  for (let i = 0; i <= steps; i++) {
    const t = i / steps;
    const alongX = start.x + dx * t;
    const alongY = start.y + dy * t;
    const offset = Math.sin(t * Math.PI * (len / wavelength)) * amplitude;
    pts.push({ x: alongX + vx * offset, y: alongY + vy * offset });
  }
  return pts;
};

const polylineLength = (points) => {
  let sum = 0;
  for (let i = 0; i < points.length - 1; i++) {
    sum += distance(points[i], points[i + 1]);
  }
  return sum;
};

const samplePolyline = (points, segmentLength = 6) => {
  if (!points || points.length === 0) return [];
  if (points.length === 1) return points;
  const samples = [points[0]];
  for (let i = 0; i < points.length - 1; i++) {
    const a = points[i];
    const b = points[i + 1];
    const len = distance(a, b);
    const steps = Math.max(1, Math.ceil(len / segmentLength));
    for (let s = 1; s <= steps; s++) {
      const t = s / steps;
      samples.push({ x: a.x + (b.x - a.x) * t, y: a.y + (b.y - a.y) * t });
    }
  }
  return samples;
};

const angleToHorizontal = (a, b) => {
  const rad = Math.atan2(b.y - a.y, b.x - a.x);
  return (rad * 180) / Math.PI;
};

const angleToVertical = (a, b) => {
  const rad = Math.atan2(b.x - a.x, a.y - b.y); // compare with positive Y
  return (rad * 180) / Math.PI;
};

export {
  clamp,
  distance,
  distancePointToSegment,
  angleRad,
  angleDeg,
  arrowHead,
  arrowHeadVectors,
  sampleQuadraticBezier,
  squigglyPoints,
  polylineLength,
  samplePolyline,
  angleToHorizontal,
  angleToVertical,
};
