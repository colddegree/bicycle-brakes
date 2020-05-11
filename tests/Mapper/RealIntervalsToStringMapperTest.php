<?php

declare(strict_types=1);

namespace App\Tests\Mapper;

use App\Entity\RealValue;
use App\Mapper\RealIntervalsToStringMapper;
use Generator;
use PHPUnit\Framework\TestCase;

class RealIntervalsToStringMapperTest extends TestCase
{
    /**
     * @dataProvider cases
     *
     * @param RealValue[] $intervals
     * @param string $expected
     */
    public function test(array $intervals, string $expected): void
    {
        $mapper = new RealIntervalsToStringMapper();

        $actual = $mapper->map($intervals);

        self::assertSame($expected, $actual);
    }

    public function cases(): Generator
    {
        yield 'сложный пример с точкой' => [
            [
                new RealValue(0, false, 2.9, false),
                new RealValue(4, true,4, true),
                new RealValue(7.77, true, 1000000000, false),
            ],
            '(0; 2,9) ∪ {4} ∪ [7,77; 1000000000)',
        ];

        yield 'только точка' => [
            [
                new RealValue(1, true,1, true),
            ],
            '{1}',
        ];

        yield 'только вырожденная точка' => [
            [
                new RealValue(1, false,1, true),
            ],
            '∅',
        ];

        yield 'интервал и вырожденная точка' => [
            [
                new RealValue(0.5, true,1, false),
                new RealValue(3, false,3, true),
            ],
            '[0,5; 1)',
        ];

        yield 'пустое множество' => [
            [],
            '∅',
        ];

        yield '2 вырожденных точки' => [
            [
                new RealValue(1, false,1, true),
                new RealValue(2, false,2, false),
            ],
            '∅',
        ];
    }
}
