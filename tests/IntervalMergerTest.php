<?php

namespace App\Tests;

use App\Entity\IntValue;
use App\IntervalMerger;
use Generator;
use PHPUnit\Framework\TestCase;

class IntervalMergerTest extends TestCase
{
    /**
     * @dataProvider intTestCases
     *
     * @param IntValue[] $intervals
     * @param IntValue[] $mergedIntervals
     */
    public function testInt(array $intervals, array $mergedIntervals): void
    {
        $merger = new IntervalMerger();

        $actual = $merger->mergeInt($intervals);

        self::assertEquals($mergedIntervals, $actual);
    }

    public function intTestCases(): Generator
    {
        yield '2 интервала пересекаются' => [
            [
                new IntValue(1, 3),
                new IntValue(-1, 2),
            ],
            [new IntValue(-1, 3)],
        ];

        yield '2 интервала касаются' => [
            [
                new IntValue(1, 3),
                new IntValue(3, 5),
            ],
            [new IntValue(1, 5)],
        ];

        yield 'первый интервал вложен во второй' => [
            [
                new IntValue(1, 3),
                new IntValue(1, 5),
            ],
            [new IntValue(1, 5)],
        ];

        yield 'второй интервал вложен в первый' => [
            [
                new IntValue(2, 4),
                new IntValue(1, 5),
            ],
            [new IntValue(1, 5)],
        ];

        yield 'первые 2 пересекаются, а третий нет' => [
            [
                new IntValue(1, 3),
                new IntValue(2, 4),
                new IntValue(5, 7),
            ],
            [
                new IntValue(1, 4),
                new IntValue(5, 7),
            ],
        ];
    }
}
