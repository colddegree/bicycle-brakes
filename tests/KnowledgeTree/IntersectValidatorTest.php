<?php

declare(strict_types=1);

namespace App\Tests\KnowledgeTree;

use App\Entity\IntValue;
use App\Entity\RealValue;
use App\KnowledgeTree\IntersectValidator;
use Generator;
use PHPUnit\Framework\TestCase;

class IntersectValidatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider intTestCases
     *
     * @param IntValue[] $as
     * @param IntValue[] $bs
     * @param bool $expected
     */
    public function int(array $as, array $bs, bool $expected): void
    {
        $validator = new IntersectValidator();

        $actual = $validator->checkAsIntersectsWithBsInt($as, $bs);

        self::assertSame($expected, $actual);
    }

    public function intTestCases(): Generator
    {
        yield 'пересекается' => [
            [
                new IntValue(0, 1),
            ],
            [
                new IntValue(1, 2),
            ],
            true,
        ];

        yield 'не пересекается' => [
            [
                new IntValue(0, 1),
            ],
            [
                new IntValue(2, 2),
            ],
            false,
        ];

        yield 'пересекается: a вложен в b' => [
            [
                new IntValue(0, 1),
            ],
            [
                new IntValue(-2, 2),
            ],
            true,
        ];

        yield 'пересекается: b вложен в a' => [
            [
                new IntValue(-2, 2),
            ],
            [
                new IntValue(0, 1),
            ],
            true,
        ];

        yield 'пересекается: a = b' => [
            [
                new IntValue(1, 2),
            ],
            [
                new IntValue(1, 2),
            ],
            true,
        ];

        yield [
            [
                new IntValue(1, 2),
                new IntValue(4, 5),
            ],
            [
                new IntValue(3, 3),
            ],
            false,
        ];

        yield [
            [
                new IntValue(4, 5),
                new IntValue(1, 2),
            ],
            [
                new IntValue(6, 100),
                new IntValue(-100, 0),
                new IntValue(3, 3),
            ],
            false,
        ];
    }

    /**
     * @test
     * @dataProvider realTestCases
     *
     * @param RealValue[] $as
     * @param RealValue[] $bs
     * @param bool $expected
     */
    public function real(array $as, array $bs, bool $expected): void
    {
        $validator = new IntersectValidator();

        $actual = $validator->checkAsIntersectsWithBsReal($as, $bs);

        self::assertSame($expected, $actual);
    }

    public function realTestCases(): Generator
    {
        yield 'пересекается' => [
            [
                new RealValue(0, true, 1, true),
            ],
            [
                new RealValue(1, true, 2, true),
            ],
            true,
        ];

        yield 'не пересекается' => [
            [
                new RealValue(0, true, 1, false),
            ],
            [
                new RealValue(1, true, 2, true),
            ],
            false,
        ];

        yield [
            [
                new RealValue(0, true, 1, true),
            ],
            [
                new RealValue(1, false, 2, true),
            ],
            false,
        ];

        yield [
            [
                new RealValue(0, true, 1, false),
            ],
            [
                new RealValue(1, false, 2, true),
            ],
            false,
        ];

        // те же 4 кейса, но в другом порядке

        yield [
            [
                new RealValue(1, true, 2, true),
            ],
            [
                new RealValue(0, true, 1, true),
            ],
            true,
        ];

        yield [
            [
                new RealValue(1, true, 2, true),
            ],
            [
                new RealValue(0, true, 1, false),
            ],
            false,
        ];

        yield [
            [
                new RealValue(1, false, 2, true),
            ],
            [
                new RealValue(0, true, 1, true),
            ],
            false,
        ];

        yield [
            [
                new RealValue(1, false, 2, true),
            ],
            [
                new RealValue(0, true, 1, false),
            ],
            false,
        ];


        // кейсы с несколькими интервалами

        yield [
            [
                new RealValue(1, false, 2, false),
                new RealValue(3, false, 4, false),
            ],
            [
                new RealValue(2, true, 3, true),
                new RealValue(4, true, 5, true),
            ],
            false,
        ];

        yield [
            [
                new RealValue(1, false, 2, true),
                new RealValue(3, false, 4, false),
            ],
            [
                new RealValue(2, true, 3, true),
                new RealValue(4, true, 5, true),
            ],
            true,
        ];

        yield [
            [
                new RealValue(1, false, 2, false),
                new RealValue(3, true, 4, false),
            ],
            [
                new RealValue(2, true, 3, true),
                new RealValue(4, true, 5, true),
            ],
            true,
        ];
    }
}
