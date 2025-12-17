<?php

use App\Support\Geometry\Geometry;

it('clamps values correctly', function () {
    expect(Geometry::clamp(5.0, 0.0, 10.0))->toBe(5.0)
        ->and(Geometry::clamp(-2.0, 0.0, 10.0))->toBe(0.0)
        ->and(Geometry::clamp(12.0, 0.0, 10.0))->toBe(10.0);
});

it('computes distance to segment', function () {
    $p = ['x' => 5.0, 'y' => 5.0];
    $a = ['x' => 0.0, 'y' => 0.0];
    $b = ['x' => 10.0, 'y' => 0.0];

    $distance = Geometry::distancePointToSegment($p, $a, $b);

    expect($distance)->toBeGreaterThan(4.9)->toBeLessThan(5.1);
});

it('computes angle in degrees', function () {
    $a = ['x' => 1.0, 'y' => 0.0];
    $b = ['x' => 0.0, 'y' => 0.0];
    $c = ['x' => 0.0, 'y' => 1.0];

    $deg = Geometry::angleDeg($a, $b, $c);

    expect($deg)->toBeGreaterThan(89.9)->toBeLessThan(90.1);
});

it('computes arrow head vectors', function () {
    $start = ['x' => 0.0, 'y' => 0.0];
    $end = ['x' => 10.0, 'y' => 0.0];
    $arrow = Geometry::arrowHead($start, $end, 12.0);

    expect($arrow['left']['x'])->toBeLessThan($end['x'])
        ->and($arrow['right']['x'])->toBeLessThan($end['x'])
        ->and($arrow['left']['y'])->not->toEqual($arrow['right']['y']);
});
