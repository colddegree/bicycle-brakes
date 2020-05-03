<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

/**
 * @ORM\Entity()
 */
class RealValue
{
    public const MIN = -1000000000.0; // 1 billion
    public const MAX = 1000000000.0; // 1 billion

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    public ?int $id = null;

    /**
     * @ORM\Column(type="float")
     */
    public float $lower;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $lowerIsInclusive;

    /**
     * @ORM\Column(type="float")
     */
    public float $upper;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $upperIsInclusive;

    public function __construct(float $lower, bool $lowerIsInclusive, float $upper, bool $upperIsInclusive)
    {
        if ($lower > $upper) {
            throw new DomainException('$lower should be less or equal to $upper');
        }
        $this->lower = $lower;
        $this->lowerIsInclusive = $lowerIsInclusive;
        $this->upper = $upper;
        $this->upperIsInclusive = $upperIsInclusive;
    }
}
