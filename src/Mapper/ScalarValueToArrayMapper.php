<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\ScalarValue;

class ScalarValueToArrayMapper
{
    public function map(ScalarValue $scalarValue): array
    {
        return [
            'id' => $scalarValue->id,
            'value' => $scalarValue->value,
        ];
    }
}
