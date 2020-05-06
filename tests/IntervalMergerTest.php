<?php

namespace App\Tests;

use App\Entity\IntValue;
use App\Entity\RealValue;
use App\IntervalMerger;
use Generator;
use PHPUnit\Framework\TestCase;

class IntervalMergerTest extends TestCase
{
    /**
     * @test
     * @dataProvider intTestCases
     *
     * @param IntValue[] $intervals
     * @param IntValue[] $mergedIntervals
     */
    public function int(array $intervals, array $mergedIntervals): void
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
                new IntValue(1, 5),
                new IntValue(2, 4),
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

        yield '2 одинаковые точки' => [
            [
                new IntValue(1, 1),
                new IntValue(1, 1),
            ],
            [new IntValue(1, 1)],
        ];

        yield '2 разные точки' => [
            [
                new IntValue(1, 1),
                new IntValue(2, 2),
            ],
            [
                new IntValue(1, 1),
                new IntValue(2, 2),
            ],
        ];

        yield 'точка внутри интервала' => [
            [
                new IntValue(1, 3),
                new IntValue(2, 2),
            ],
            [new IntValue(1, 3)],
        ];

        yield 'интервал и точка снаружи' => [
            [
                new IntValue(1, 3),
                new IntValue(4, 4),
            ],
            [
                new IntValue(1, 3),
                new IntValue(4, 4),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider realTestCases
     *
     * @param RealValue[] $intervals
     * @param RealValue[] $mergedIntervals
     */
    public function real(array $intervals, array $mergedIntervals): void
    {
        $merger = new IntervalMerger();

        $actual = $merger->mergeReal($intervals);

        self::assertEquals($mergedIntervals, $actual);
    }

    public function realTestCases(): Generator
    {
        yield '2 интервала пересекаются' => [
            [
                new RealValue(1, false, 3, false),
                new RealValue(-1, false,2, false),
            ],
            [new RealValue(-1, false, 3, false)],
        ];

        yield 'общее начало, но у первого нижняя граница не включительна' => [
            [
                new RealValue(1, false, 2, false),
                new RealValue(1, true,5, false),
            ],
            [new RealValue(1, true, 5, false)],
        ];

        yield 'конец первого интервала касается начала второго интервала, но оба не включительны' => [
            [
                new RealValue(1, true, 2, false),
                new RealValue(2, false,3, true),
            ],
            [
                new RealValue(1, true, 2, false),
                new RealValue(2, false,3, true),
            ],
        ];

        yield 'конец первого интервала касается начала второго интервала и только первый включительный' => [
            [
                new RealValue(1, true, 2, true),
                new RealValue(2, false,3, true),
            ],
            [new RealValue(1, true, 3, true)],
        ];

        yield 'конец первого интервала касается начала второго интервала и только второй включительный' => [
            [
                new RealValue(1, true, 2, false),
                new RealValue(2, true,3, true),
            ],
            [new RealValue(1, true, 3, true)],
        ];

        yield 'первый невключительный интервал вложен во второй включительный' => [
            [
                new RealValue(2, false, 3, false),
                new RealValue(1, true,5, true),
            ],
            [new RealValue(1, true, 5, true)],
        ];

        yield 'первый невключительный интервал пересекается "справа" со вторым включительным интервалом' => [
            [
                new RealValue(4, false, 6, false),
                new RealValue(1, true,5, true),
            ],
            [new RealValue(1, true, 6, false)],
        ];

        yield '2 интервала с одинаковыми невключительными нижними границами' => [
            [
                new RealValue(1, false, 5, false),
                new RealValue(1, false,3, false),
            ],
            [new RealValue(1, false, 5, false)],
        ];

        yield '2 интервала с одинаковыми включительными нижними границами' => [
            [
                new RealValue(1, true, 5, false),
                new RealValue(1, true,3, false),
            ],
            [new RealValue(1, true, 5, false)],
        ];

        yield 'интервал и вырожденная точка (хотя бы одна сторона невключительна) снаружи' => [
            [
                new RealValue(1, true, 3, false),
                new RealValue(4, false,4, true),
            ],
            [new RealValue(1, true, 3, false)],
        ];

        yield 'интервал и невырожденная точка снаружи' => [
            [
                new RealValue(1, true, 3, false),
                new RealValue(4, true,4, true),
            ],
            [
                new RealValue(1, true, 3, false),
                new RealValue(4, true,4, true),
            ],
        ];

        yield 'интервал и невырожденная точка внутри него' => [
            [
                new RealValue(1, true, 3, false),
                new RealValue(2, true,2, true),
            ],
            [new RealValue(1, true, 3, false)],
        ];
    }
}
