import { distancePointToSegment, distance, sampleQuadraticBezier, samplePolyline, squigglyPoints } from "./geometry";

const BASE_HIT_THRESHOLD_SCREEN = 12; // screen px

const toVideoThreshold = (scale) => {
  if (!scale || scale <= 0) return BASE_HIT_THRESHOLD_SCREEN;
  return BASE_HIT_THRESHOLD_SCREEN / scale;
};

const withStyleThreshold = (scale, drawing) => {
  const lineWidth = drawing?.style?.lineWidth ?? 2;
  const dynamicScreen = Math.max(BASE_HIT_THRESHOLD_SCREEN, lineWidth * 2);
  const thresholdVideo = scale && scale > 0 ? dynamicScreen / scale : dynamicScreen;
  return thresholdVideo;
};

const hitPen = (point, drawing, scale) => {
  const pts = drawing.geometry.path || [];
  const threshold = withStyleThreshold(scale, drawing);
  for (let i = 0; i < pts.length - 1; i++) {
    if (distancePointToSegment(point, pts[i], pts[i + 1]) <= threshold) return true;
  }
  return false;
};

const hitSampled = (point, samples, drawing, scale) => {
  const threshold = withStyleThreshold(scale, drawing);
  for (let i = 0; i < samples.length - 1; i++) {
    if (distancePointToSegment(point, samples[i], samples[i + 1]) <= threshold) return true;
  }
  return false;
};

const hitLine = (point, drawing, scale) => {
  const { start, end } = drawing.geometry;
  return distancePointToSegment(point, start, end) <= withStyleThreshold(scale, drawing);
};

const hitMarker = (point, drawing, scale) => {
  return distance(point, drawing.geometry.position) <= withStyleThreshold(scale, drawing);
};

const hitText = (point, drawing, scale) => distance(point, drawing.geometry.position) <= toVideoThreshold(scale);

const hitAngle = (point, drawing, scale) => {
  const threshold = withStyleThreshold(scale, drawing);
  const pts = drawing.geometry.points ?? [];
  if (!pts.length) return false;
  if (drawing.variant && drawing.variant !== "three_point") {
    // 2点のみ
    return pts.some((p) => distance(point, p) <= threshold) || (pts[1] && distancePointToSegment(point, pts[0], pts[1]) <= threshold);
  }
  return pts.some((p) => distance(point, p) <= threshold) || distancePointToSegment(point, pts[0], pts[1]) <= threshold || distancePointToSegment(point, pts[1], pts[2]) <= threshold;
};

const hitCircle = (point, drawing, scale) => {
  const threshold = withStyleThreshold(scale, drawing);
  const center = drawing.geometry.center;
  const radius = drawing.geometry.radius ?? 0;
  const dist = distance(point, center);
  return Math.abs(dist - radius) <= threshold;
};

const hitTrack = (point, drawing, scale) => {
  const threshold = withStyleThreshold(scale, drawing);
  const pts = drawing.geometry.path ?? [];
  for (let i = 0; i < pts.length - 1; i++) {
    if (distancePointToSegment(point, pts[i], pts[i + 1]) <= threshold) return true;
  }
  return false;
};

const hitStamp = (point, drawing, scale) => hitMarker(point, drawing, scale);

const hitTest = (videoPoint, drawing, scale) => {
  switch (drawing.type) {
    case "pen":
      return hitPen(videoPoint, drawing, scale);
    case "line":
      return hitLine(videoPoint, drawing, scale);
    case "arrow": {
      const variant = drawing.variant ?? "normal";
      const g = drawing.geometry;
      if (variant === "curve") {
        const samples = sampleQuadraticBezier(g.start, g.control ?? g.end, g.end);
        return hitSampled(videoPoint, samples, drawing, scale);
      }
      if (variant.startsWith("polyline")) {
        const pts = g.points ?? [g.start, g.end];
        let samples;
        if (variant === "polyline_squiggly") {
          samples = [];
          for (let i = 0; i < pts.length - 1; i++) {
            const seg = squigglyPoints(
              pts[i],
              pts[i + 1],
              drawing.style?.squiggleWavelength ?? 12,
              drawing.style?.squiggleAmplitude ?? 4
            );
            if (i > 0) seg.shift();
            samples.push(...seg);
          }
        } else {
          samples = samplePolyline(pts);
        }
        return hitSampled(videoPoint, samples, drawing, scale);
      }
      if (variant === "squiggly") {
        const samples = squigglyPoints(g.start, g.end, drawing.style?.squiggleWavelength ?? 12, drawing.style?.squiggleAmplitude ?? 4);
        return hitSampled(videoPoint, samples, drawing, scale);
      }
      if (variant === "dash") return hitLine(videoPoint, drawing, scale);
      return hitLine(videoPoint, drawing, scale);
    }
    case "marker":
      return hitMarker(videoPoint, drawing, scale);
    case "text":
    case "autonumber":
      return hitText(videoPoint, drawing, scale);
    case "angle":
      return hitAngle(videoPoint, drawing, scale);
    case "circle":
      return hitCircle(videoPoint, drawing, scale);
    case "track":
      return hitTrack(videoPoint, drawing, scale);
    case "stamp":
      return hitStamp(videoPoint, drawing, scale);
    default:
      return false;
  }
};

export { hitTest, toVideoThreshold, withStyleThreshold };
