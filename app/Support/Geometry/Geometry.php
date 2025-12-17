<?php

namespace App\Support\Geometry;

final class Geometry
{
    public static function clamp(float $value, float $min, float $max): float
    {
        return min(max($value, $min), $max);
    }

    /**
     * @param  array{x: float, y: float}  $p1
     * @param  array{x: float, y: float}  $p2
     */
    public static function distance(array $p1, array $p2): float
    {
        return hypot($p1['x'] - $p2['x'], $p1['y'] - $p2['y']);
    }

    /**
     * @param  array{x: float, y: float}  $p
     * @param  array{x: float, y: float}  $a
     * @param  array{x: float, y: float}  $b
     */
    public static function distancePointToSegment(array $p, array $a, array $b): float
    {
        $ab = ['x' => $b['x'] - $a['x'], 'y' => $b['y'] - $a['y']];
        $ap = ['x' => $p['x'] - $a['x'], 'y' => $p['y'] - $a['y']];
        $ab2 = $ab['x'] ** 2 + $ab['y'] ** 2;

        if ($ab2 === 0.0) {
            return self::distance($p, $a);
        }

        $t = self::clamp(($ap['x'] * $ab['x'] + $ap['y'] * $ab['y']) / $ab2, 0.0, 1.0);
        $proj = [
            'x' => $a['x'] + $ab['x'] * $t,
            'y' => $a['y'] + $ab['y'] * $t,
        ];

        return self::distance($p, $proj);
    }

    /**
     * @param  array{x: float, y: float}  $a
     * @param  array{x: float, y: float}  $b
     * @param  array{x: float, y: float}  $c
     */
    public static function angleRad(array $a, array $b, array $c): float
    {
        $ab = ['x' => $a['x'] - $b['x'], 'y' => $a['y'] - $b['y']];
        $cb = ['x' => $c['x'] - $b['x'], 'y' => $c['y'] - $b['y']];

        $dot = $ab['x'] * $cb['x'] + $ab['y'] * $cb['y'];
        $mag = sqrt($ab['x'] ** 2 + $ab['y'] ** 2) * sqrt($cb['x'] ** 2 + $cb['y'] ** 2);

        if ($mag === 0.0) {
            return 0.0;
        }

        $cos = self::clamp($dot / $mag, -1.0, 1.0);

        return acos($cos);
    }

    /**
     * @param  array{x: float, y: float}  $a
     * @param  array{x: float, y: float}  $b
     * @param  array{x: float, y: float}  $c
     */
    public static function angleDeg(array $a, array $b, array $c): float
    {
        return (self::angleRad($a, $b, $c) * 180.0) / M_PI;
    }

    /**
     * @param  array{x: float, y: float}  $start
     * @param  array{x: float, y: float}  $end
     * @return array{left: array{x: float, y: float}, right: array{x: float, y: float}}
     */
    public static function arrowHead(array $start, array $end, float $size = 12.0): array
    {
        $dx = $end['x'] - $start['x'];
        $dy = $end['y'] - $start['y'];
        $length = hypot($dx, $dy) ?: 1.0;
        $ux = $dx / $length;
        $uy = $dy / $length;

        return [
            'left' => [
                'x' => $end['x'] - $ux * $size + $uy * ($size * 0.6),
                'y' => $end['y'] - $uy * $size - $ux * ($size * 0.6),
            ],
            'right' => [
                'x' => $end['x'] - $ux * $size - $uy * ($size * 0.6),
                'y' => $end['y'] - $uy * $size + $ux * ($size * 0.6),
            ],
        ];
    }
}
