<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\IntValue;

class IntIntervalsToStringMapper
{
    /**
     * @param IntValue[] $intervals
     *
     * @return string
     */
    public function map(array $intervals): string
    {
        $intervalStrings = array_map(fn(IntValue $int) => $this->mapIntervalToString($int), $intervals);
        return implode(' ∪ ', $intervalStrings);
    }

    private function mapIntervalToString(IntValue $int): string
    {
        if ($int->lower === $int->upper) {
            return sprintf('{%d}', $int->lower);
        }
        return sprintf('[%d; %d]', $int->lower, $int->upper);
    }
}
