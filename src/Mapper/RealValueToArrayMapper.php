<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\RealValue;

class RealValueToArrayMapper
{
    public function map(RealValue $realValue): array
    {
        return [
            'id' => $realValue->id,
            'lower' => $realValue->lower,
            'lowerIsInclusive' => $realValue->lowerIsInclusive,
            'upper' => $realValue->upper,
            'upperIsInclusive' => $realValue->upperIsInclusive,
        ];
    }
}
