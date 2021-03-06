<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class IntValue
{
    public const MIN = -9223372036854775808; // 8 byte signed int min value
    public const MAX = 9223372036854775807; // 8 byte signed int max value

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    public ?int $id = null;

    /**
     * @ORM\Column(type="bigint")
     */
    public int $lower;

    /**
     * @ORM\Column(type="bigint")
     */
    public int $upper;

    public function __construct(int $lower, int $upper)
    {
        if ($lower > $upper) {
            $this->lower = $upper;
            $this->upper = $lower;
        } else {
            $this->lower = $lower;
            $this->upper = $upper;
        }
    }
}
