<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\RealValue;

class RealIntervalsToStringMapper
{
    /**
     * @param RealValue[] $intervals
     *
     * @return string
     */
    public function map(array $intervals): string
    {
        return implode(' ∪ ', array_map(fn (RealValue $int) => $this->mapIntervalToString($int), $intervals));
    }

    private function mapIntervalToString(RealValue $interval): string
    {
        $result = '';

        if ($interval->lowerIsInclusive) {
            $result .= '[';
        } else {
            $result .= '(';
        }

        $result .= sprintf('%s; %s', $interval->lower, $interval->upper); // TODO: check, mb need number_format

        if ($interval->upperIsInclusive) {
            $result .= ']';
        } else {
            $result .= ')';
        }

        return $result;
    }
}
