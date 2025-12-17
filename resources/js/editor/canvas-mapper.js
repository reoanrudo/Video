const DEFAULT_VIDEO_WIDTH = 1920;
const DEFAULT_VIDEO_HEIGHT = 1080;

const computeVideoRectInCanvas = (canvasCssWidth, canvasCssHeight, videoWidth, videoHeight) => {
  if (videoWidth <= 0 || videoHeight <= 0) {
    return {
      drawWidth: 0,
      drawHeight: 0,
      offsetX: 0,
      offsetY: 0,
      scale: 0,
    };
  }

  const scale = Math.min(canvasCssWidth / videoWidth, canvasCssHeight / videoHeight);
  const drawWidth = videoWidth * scale;
  const drawHeight = videoHeight * scale;
  const offsetX = (canvasCssWidth - drawWidth) / 2;
  const offsetY = (canvasCssHeight - drawHeight) / 2;

  return {
    drawWidth,
    drawHeight,
    offsetX,
    offsetY,
    scale,
  };
};

const videoToCanvas = (vx, vy, rect, devicePixelRatio) => {
  const { scale, offsetX, offsetY } = rect;

  return {
    cx: (offsetX + vx * scale) * devicePixelRatio,
    cy: (offsetY + vy * scale) * devicePixelRatio,
  };
};

const canvasToVideo = (cx, cy, rect, devicePixelRatio, videoWidth, videoHeight) => {
  const { scale, offsetX, offsetY } = rect;

  if (scale === 0) {
    return { vx: 0, vy: 0 };
  }

  const xCss = cx / devicePixelRatio;
  const yCss = cy / devicePixelRatio;
  const vx = (xCss - offsetX) / scale;
  const vy = (yCss - offsetY) / scale;

  return {
    vx: Math.min(Math.max(vx, 0), videoWidth),
    vy: Math.min(Math.max(vy, 0), videoHeight),
  };
};

const buildMapper = (canvas, video, fallbackSize = { width: DEFAULT_VIDEO_WIDTH, height: DEFAULT_VIDEO_HEIGHT }) => {
  const dpr = window.devicePixelRatio || 1;
  const canvasCssWidth = canvas?.clientWidth ?? 0;
  const canvasCssHeight = canvas?.clientHeight ?? 0;
  const videoWidth = video?.videoWidth || fallbackSize.width;
  const videoHeight = video?.videoHeight || fallbackSize.height;
  const rect = computeVideoRectInCanvas(canvasCssWidth, canvasCssHeight, videoWidth, videoHeight);

  const backingWidth = Math.max(1, Math.round(canvasCssWidth * dpr));
  const backingHeight = Math.max(1, Math.round(canvasCssHeight * dpr));
  if (canvas && (canvas.width !== backingWidth || canvas.height !== backingHeight)) {
    canvas.width = backingWidth;
    canvas.height = backingHeight;
  }

  return {
    rect,
    dpr,
    videoWidth,
    videoHeight,
    canvasCssWidth,
    canvasCssHeight,
    canvasWidth: canvas?.width ?? 0,
    canvasHeight: canvas?.height ?? 0,
  };
};

export { computeVideoRectInCanvas, videoToCanvas, canvasToVideo, buildMapper, DEFAULT_VIDEO_WIDTH, DEFAULT_VIDEO_HEIGHT };
