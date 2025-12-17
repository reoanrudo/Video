import { distancePointToSegment, distance } from "./geometry";

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
  const pts = drawing.geometry.points;
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
    case "arrow":
      return hitLine(videoPoint, drawing, scale);
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
