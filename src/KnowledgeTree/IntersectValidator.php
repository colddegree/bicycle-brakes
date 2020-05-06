<?php

declare(strict_types=1);

namespace App\KnowledgeTree;

use App\Entity\IntValue;

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
        return $a->upper >= $b->lower && $a->lower <= $b->upper;
    }
}
