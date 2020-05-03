<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\IntValue;

class IntValueToArrayMapper
{
    public function map(IntValue $intValue): array
    {
        return [
            'id' => $intValue->id,
            'lower' => $intValue->lower,
            'upper' => $intValue->upper,
        ];
    }
}
