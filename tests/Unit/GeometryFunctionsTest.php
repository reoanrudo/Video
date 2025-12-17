<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class GeometryFunctionsTest extends TestCase
{
    private function distance(array $a, array $b): float
    {
        return hypot($a['x'] - $b['x'], $a['y'] - $b['y']);
    }

    private function squigglyPoints(array $start, array $end, float $wavelength = 12, float $amplitude = 4): array
    {
        $dx = $end['x'] - $start['x'];
        $dy = $end['y'] - $start['y'];
        $len = hypot($dx, $dy) ?: 1;
        $ux = $dx / $len;
        $uy = $dy / $len;
        $vx = -$uy;
        $vy = $ux;

        $step = max(4, $wavelength / 2);
        $steps = max(2, (int)ceil($len / $step));
        $pts = [];
        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $alongX = $start['x'] + $dx * $t;
            $alongY = $start['y'] + $dy * $t;
            $offset = sin($t * M_PI * ($len / $wavelength)) * $amplitude;
            $pts[] = ['x' => $alongX + $vx * $offset, 'y' => $alongY + $vy * $offset];
        }
        return $pts;
    }

    private function sampleQuadraticBezier(array $start, array $control, array $end, float $segmentLength = 6): array
    {
        $approxLen = $this->distance($start, $control) + $this->distance($control, $end);
        $steps = max(2, (int)ceil($approxLen / $segmentLength));
        $pts = [];
        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $mt = 1 - $t;
            $x = $mt * $mt * $start['x'] + 2 * $mt * $t * $control['x'] + $t * $t * $end['x'];
            $y = $mt * $mt * $start['y'] + 2 * $mt * $t * $control['y'] + $t * $t * $end['y'];
            $pts[] = ['x' => $x, 'y' => $y];
        }
        return $pts;
    }

    private function arrowHeadVectors(array $start, array $end, float $size = 12): array
    {
        $dx = $end['x'] - $start['x'];
        $dy = $end['y'] - $start['y'];
        $len = hypot($dx, $dy) ?: 1;
        $ux = $dx / $len;
        $uy = $dy / $len;
        $left = ['x' => -$ux * $size + $uy * ($size * 0.6), 'y' => -$uy * $size - $ux * ($size * 0.6)];
        $right = ['x' => -$ux * $size - $uy * ($size * 0.6), 'y' => -$uy * $size + $ux * ($size * 0.6)];
        return ['left' => $left, 'right' => $right];
    }

    private function angleToHorizontal(array $a, array $b): float
    {
        $rad = atan2($b['y'] - $a['y'], $b['x'] - $a['x']);
        return ($rad * 180) / M_PI;
    }

    private function angleToVertical(array $a, array $b): float
    {
        $rad = atan2($b['x'] - $a['x'], $a['y'] - $b['y']);
        return ($rad * 180) / M_PI;
    }

    public function testSquigglyPointsKeepsEndpointsAndOscillates()
    {
        $pts = $this->squigglyPoints(['x' => 0, 'y' => 0], ['x' => 10, 'y' => 0], 4, 2);
        $this->assertSame(0.0, $pts[0]['x']);
        $this->assertSame(0.0, $pts[0]['y']);
        $this->assertSame(10.0, $pts[array_key_last($pts)]['x']);
        $this->assertLessThanOrEqual(2.0, abs($pts[array_key_last($pts)]['y']));
        $midY = $pts[(int)floor(count($pts) / 2)]['y'];
        $this->assertNotEquals(0.0, $midY);
    }

    public function testSampleQuadraticBezierProducesExpectedCount()
    {
        $pts = $this->sampleQuadraticBezier(['x' => 0, 'y' => 0], ['x' => 0, 'y' => 10], ['x' => 10, 'y' => 10], 5);
        $this->assertCount(5, $pts);
        $this->assertEqualsWithDelta(0, $pts[0]['x'], 0.001);
        $this->assertEqualsWithDelta(10, $pts[array_key_last($pts)]['y'], 0.001);
    }

    public function testArrowHeadVectorsOrientation()
    {
        $vecs = $this->arrowHeadVectors(['x' => 0, 'y' => 0], ['x' => 10, 'y' => 10], 10);
        $this->assertLessThan(0, $vecs['left']['x']);
        $this->assertLessThan(0, $vecs['left']['y']);
        $this->assertLessThan(0, $vecs['right']['x']);
        $this->assertLessThanOrEqual(0, $vecs['right']['y']);
        $this->assertNotEquals(0.0, $vecs['right']['y']);
    }

    public function testAngleToHorizontal()
    {
        $deg = $this->angleToHorizontal(['x' => 0, 'y' => 0], ['x' => 0, 'y' => 10]);
        $this->assertEqualsWithDelta(90, $deg, 0.01);
    }

    public function testAngleToVertical()
    {
        $deg = $this->angleToVertical(['x' => 0, 'y' => 0], ['x' => 0, 'y' => -10]);
        $this->assertEqualsWithDelta(0, $deg, 0.01);
    }
}
