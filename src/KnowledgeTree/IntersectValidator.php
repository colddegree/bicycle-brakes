<?php

declare(strict_types=1);

namespace App\KnowledgeTree;

use App\Entity\IntValue;
use App\Entity\RealValue;

class IntersectValidator
{
    /**
     * @param IntValue[] $as
     * @param IntValue[] $bs
     *
     * @return bool
     */
    public function checkAsIntersectsWithBsInt(array $as, array $bs): bool
    {
        foreach ($as as $a) {
            foreach ($bs as $b) {
                if ($this->aIntersectsWithBInt($a, $b)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function aIntersectsWithBInt(IntValue $a, IntValue $b): bool
    {
        if ($a->upper < $b->lower || $a->lower > $b->upper) {
            return false;
        }
        return true;
    }

    /**
     * @param RealValue[] $as
     * @param RealValue[] $bs
     *
     * @return bool
     */
    public function checkAsIntersectsWithBsReal(array $as, array $bs): bool
    {
        foreach ($as as $a) {
            foreach ($bs as $b) {
                if ($this->aIntersectsWithBReal($a, $b)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function aIntersectsWithBReal(RealValue $a, RealValue $b): bool
    {
        if ($a->upper === $b->lower && $a->upperIsInclusive && $b->lowerIsInclusive) {
            return true;
        }

        if ($a->lower === $b->upper && $a->lowerIsInclusive && $b->upperIsInclusive) {
            return true;
        }

        if ($a->upper <= $b->lower || $a->lower >= $b->upper) {
            return false;
        }
        return true;
    }
}
