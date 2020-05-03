<?php

declare(strict_types=1);

namespace App;

use App\Entity\IntValue;
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
}
