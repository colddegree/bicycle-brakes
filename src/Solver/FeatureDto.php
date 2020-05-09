<?php

declare(strict_types=1);

namespace App\Solver;

class FeatureDto
{
    public int $featureId;
    public string $value;

    public function __construct(int $featureId, string $value)
    {
        $this->featureId = $featureId;
        $this->value = $value;
    }
}
