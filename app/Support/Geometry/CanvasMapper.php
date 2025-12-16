<?php

namespace App\Support\Geometry;

final class CanvasMapper
{
    /**
     * @return array{
     *     draw_width: float,
     *     draw_height: float,
     *     offset_x: float,
     *     offset_y: float,
     *     scale: float
     * }
     */
    public static function computeVideoRectInCanvas(
        float $canvasCssWidth,
        float $canvasCssHeight,
        float $videoWidth,
        float $videoHeight
    ): array {
        if ($videoWidth <= 0.0 || $videoHeight <= 0.0) {
            return [
                'draw_width' => 0.0,
                'draw_height' => 0.0,
                'offset_x' => 0.0,
                'offset_y' => 0.0,
                'scale' => 0.0,
            ];
        }

        $scale = min($canvasCssWidth / $videoWidth, $canvasCssHeight / $videoHeight);
        $drawWidth = $videoWidth * $scale;
        $drawHeight = $videoHeight * $scale;
        $offsetX = ($canvasCssWidth - $drawWidth) / 2;
        $offsetY = ($canvasCssHeight - $drawHeight) / 2;

        return [
            'draw_width' => $drawWidth,
            'draw_height' => $drawHeight,
            'offset_x' => $offsetX,
            'offset_y' => $offsetY,
            'scale' => $scale,
        ];
    }

    /**
     * @return array{cx: float, cy: float}
     */
    public static function videoToCanvas(
        float $vx,
        float $vy,
        array $rect,
        float $devicePixelRatio
    ): array {
        $scale = $rect['scale'] ?? 0.0;

        $cx = ($rect['offset_x'] + $vx * $scale) * $devicePixelRatio;
        $cy = ($rect['offset_y'] + $vy * $scale) * $devicePixelRatio;

        return [
            'cx' => $cx,
            'cy' => $cy,
        ];
    }

    /**
     * @return array{vx: float, vy: float}
     */
    public static function canvasToVideo(
        float $cx,
        float $cy,
        array $rect,
        float $devicePixelRatio,
        float $videoWidth,
        float $videoHeight
    ): array {
        $scale = $rect['scale'] ?? 0.0;

        if ($scale === 0.0) {
            return ['vx' => 0.0, 'vy' => 0.0];
        }

        $xCss = $cx / $devicePixelRatio;
        $yCss = $cy / $devicePixelRatio;

        $vx = ($xCss - $rect['offset_x']) / $scale;
        $vy = ($yCss - $rect['offset_y']) / $scale;

        return [
            'vx' => self::clampValue($vx, 0.0, $videoWidth),
            'vy' => self::clampValue($vy, 0.0, $videoHeight),
        ];
    }

    private static function clampValue(float $value, float $min, float $max): float
    {
        return min(max($value, $min), $max);
    }
}
