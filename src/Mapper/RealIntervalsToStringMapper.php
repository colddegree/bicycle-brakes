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
        $intervalStrings = array_map(fn(RealValue $int) => $this->mapIntervalToString($int), $intervals);
        $intervalStrings = array_filter($intervalStrings);

        if (empty($intervalStrings)) {
            return '∅';
        }

        return implode(' ∪ ', $intervalStrings);
    }

    private function mapIntervalToString(RealValue $int): string
    {
        if ($int->lower === $int->upper && (!$int->lowerIsInclusive || !$int->upperIsInclusive)) {
            return '';
        }

        if ($int->lower === $int->upper) {
            return sprintf('{%s}', $this->mapFloatToString($int->lower));
        }

        $result = '';

        if ($int->lowerIsInclusive) {
            $result .= '[';
        } else {
            $result .= '(';
        }

        $result .= sprintf(
            '%s; %s',
            $this->mapFloatToString($int->lower),
            $this->mapFloatToString($int->upper),
        );

        if ($int->upperIsInclusive) {
            $result .= ']';
        } else {
            $result .= ')';
        }

        return $result;
    }

    private function mapFloatToString(float $f): string
    {
        $result = str_replace('.', ',', (string)$f);
        if ($result === '0' || !strpos(',', $result)) {
            return $result;
        }
        return rtrim($result, '0');
    }
}
