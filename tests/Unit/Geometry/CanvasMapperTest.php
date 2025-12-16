<?php

use App\Support\Geometry\CanvasMapper;

it('computes rectangle for landscape video', function () {
    $rect = CanvasMapper::computeVideoRectInCanvas(200, 100, 300, 150);

    expect(abs($rect['scale'] - (2.0 / 3.0)))->toBeLessThan(0.0001);
    expect(abs($rect['draw_width'] - 200))->toBeLessThan(0.1);
    expect(abs($rect['draw_height'] - 100))->toBeLessThan(0.1);
    expect(abs($rect['offset_x'] - 0))->toBeLessThan(0.1);
    expect(abs($rect['offset_y'] - 0))->toBeLessThan(0.1);
});

it('computes rectangle for portrait video', function () {
    $rect = CanvasMapper::computeVideoRectInCanvas(200, 100, 100, 300);

    expect(abs($rect['scale'] - (1.0 / 3.0)))->toBeLessThan(0.0001);
    expect(abs($rect['draw_width'] - 33.3333))->toBeLessThan(0.1);
    expect(abs($rect['draw_height'] - 100))->toBeLessThan(0.1);
    expect(abs($rect['offset_x'] - 83.3333))->toBeLessThan(0.1);
    expect(abs($rect['offset_y'] - 0))->toBeLessThan(0.1);
});

it('round trips through canvas conversions', function () {
    $rect = CanvasMapper::computeVideoRectInCanvas(160, 90, 100, 50);
    $devicePixelRatio = 2.0;
    $point = CanvasMapper::videoToCanvas(40, 25, $rect, $devicePixelRatio);
    $round = CanvasMapper::canvasToVideo($point['cx'], $point['cy'], $rect, $devicePixelRatio, 100, 50);

    expect(abs($round['vx'] - 40))->toBeLessThan(0.01);
    expect(abs($round['vy'] - 25))->toBeLessThan(0.01);
});
