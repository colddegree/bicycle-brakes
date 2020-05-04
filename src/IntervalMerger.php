<?php

declare(strict_types=1);

namespace App;

use App\Entity\IntValue;
use App\Entity\RealValue;
use SplStack;

class IntervalMerger
{
    /**
     * @param IntValue[] $intervals
     *
     * @return IntValue[]
     */
    public function mergeInt(array $intervals): array
    {
        if (count($intervals) <= 1) {
            return $intervals;
        }

        usort($intervals, static fn (IntValue $a, IntValue $b) => $a->lower <=> $b->lower);

        $stack = new SplStack();
        $stack->push($intervals[0]);

        for ($i = 1, $iMax = count($intervals); $i < $iMax; ++$i) {
            /** @var IntValue $topInterval */
            $topInterval = $stack->top();
            if ($topInterval->upper >= $intervals[$i]->lower) {
                $stack->pop();
                $stack->push(new IntValue($topInterval->lower, max($intervals[$i]->upper, $topInterval->upper)));
            } else {
                $stack->push($intervals[$i]);
            }
        }

        return array_reverse(iterator_to_array($stack));
    }

    /**
     * @param RealValue[] $intervals
     *
     * @return RealValue[]
     */
    public function mergeReal(array $intervals): array
    {
        if (count($intervals) <= 1) {
            return $intervals;
        }

        $intervals = $this->sortRealIntervals($intervals);

        $stack = new SplStack();
        $stack->push($intervals[0]);

        foreach (array_slice($intervals, 1, count($intervals)) as $int) {
            // пропускаем вырожденные точки
            if ($int->lower === $int->upper && (!$int->lowerIsInclusive || !$int->upperIsInclusive)) {
                continue;
            }

            /** @var RealValue $topInterval */
            $topInterval = $stack->top();

            if ($topInterval->upper > $int->lower) {
                $stack->pop();
                $stack->push(new RealValue(
                    $topInterval->lower,
                    $topInterval->lowerIsInclusive,
                    max($int->upper, $topInterval->upper),
                    $int->upper > $topInterval->upper
                        ? $int->upperIsInclusive
                        : $topInterval->upperIsInclusive,
                ));
            } else {
                $stack->push($int);
            }
        }

        return array_reverse(iterator_to_array($stack));
    }

    /**
     * @param RealValue[] $intervals
     *
     * @return RealValue[]
     */
    private function sortRealIntervals(array $intervals): array
    {
        $lowerBounds = array_map(static fn (RealValue $v) => $v->lower, $intervals);
        $lowerBoundInclusiveFlags = array_map(static fn (RealValue $v) => (int)$v->lowerIsInclusive, $intervals);
        $indices = array_keys($intervals);

        array_multisort(
            $lowerBounds, SORT_ASC, SORT_NUMERIC,
            $lowerBoundInclusiveFlags, SORT_DESC, SORT_NUMERIC,
            $indices, SORT_ASC, SORT_NUMERIC,
        );

        $result = [];
        foreach ($indices as $index) {
            $result[] = $intervals[$index];
        }
        return $result;
    }
}
