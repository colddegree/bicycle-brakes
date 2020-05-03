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
        return implode(' ∪ ', array_map(fn (IntValue $int) => sprintf('[%s; %s]', $int->lower, $int->upper), $intervals));
    }
}
