<?php

declare(strict_types=1);

namespace App\KnowledgeTree;

use App\Entity\IntValue;
use App\IntervalMerger;

class SubsetValidator
{
    private IntervalMerger $intervalMerger;

    public function __construct(IntervalMerger $intervalMerger)
    {
        $this->intervalMerger = $intervalMerger;
    }

    /**
     * @param IntValue[] $as
     * @param IntValue[] $bs
     *
     * @return bool
     */
    public function checkAsAreSubsetOfBsInt(array $as, array $bs): bool
    {
        $bs = $this->intervalMerger->mergeInt($bs);

        foreach ($as as $a) {
            $success = false;
            foreach ($bs as $b) {
                if ($this->isAFitsInBInt($a, $b)) {
                    $success = true;
                    break;
                }
            }
            if (!$success) {
                return false;
            }
        }
        return true;
    }

    private function isAFitsInBInt(IntValue $a, IntValue $b): bool
    {
        return $a->lower >= $b->lower && $a->upper <= $b->upper;
    }
}
