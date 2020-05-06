<?php

declare(strict_types=1);

namespace App\Tests\KnowledgeTree;

use App\Entity\IntValue;
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
}
