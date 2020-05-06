<?php

declare(strict_types=1);

namespace App\Tests\KnowledgeTree;

use App\Entity\IntValue;
use App\IntervalMerger;
use App\KnowledgeTree\SubsetValidator;
use Generator;
use PHPUnit\Framework\TestCase;

class SubsetValidatorTest extends TestCase
{
    /**
     * @dataProvider intTestCases
     *
     * @param IntValue[] $as
     * @param IntValue[] $bs
     * @param bool $expected
     */
    public function testInt(array $as, array $bs, bool $expected): void
    {
        $validator = new SubsetValidator(new IntervalMerger());

        $actual = $validator->checkAsAreSubsetOfBsInt($as, $bs);

        self::assertSame($expected, $actual);
    }

    public function intTestCases(): Generator
    {
        yield 'по одному с каждой стороны и оба равны' => [
            [new IntValue(-1, 1)],
            [new IntValue(-1, 1)],
            true
        ];

        yield 'по одному с каждой стороны, и a вписывается в b' => [
            [new IntValue(-1, 1)],
            [new IntValue(-10, 10)],
            true,
        ];

        yield [
            [new IntValue(-1, 1)],
            [new IntValue(0, 0)],
            false,
        ];

        yield [
            [new IntValue(-1, 1)],
            [new IntValue(0, 2)],
            false,
        ];

        yield [
            [new IntValue(-1, 1)],
            [new IntValue(-1, 0)],
            false,
        ];

        yield [
            [new IntValue(-1, 2)],
            [new IntValue(-1, 2)],
            true,
        ];

        yield 'по одному с каждой стороны и b вылезает за границы a' => [
            [new IntValue(-1, 2)],
            [new IntValue(-2, 3)],
            true,
        ];

        yield [
            [new IntValue(-1, 2)],
            [new IntValue(-1, 3)],
            true,
        ];

        yield [
            [
                new IntValue(-1, 2),
                new IntValue(6, 7),
                new IntValue(9, 124),
            ],
            [
                new IntValue(-1000, 1000),
            ],
            true,
        ];

        yield 'множество пустых интервалов является подмножеством пустых интервалов' => [
            [],
            [],
            true,
        ];

        yield 'a вписывается в b, разбитый на 2 касающихся интервала' => [
            [
                new IntValue(1, 7),
            ],
            [
                new IntValue(1, 3),
                new IntValue(3, 7),
            ],
            true,
        ];

        yield 'a не вписывается в b, так как b разбитый на 2 некасающихся интервала' => [
            [
                new IntValue(1, 7),
            ],
            [
                new IntValue(1, 2),
                new IntValue(3, 7),
            ],
            false,
        ];
    }
}
