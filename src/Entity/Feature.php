<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @UniqueEntity(fields={"name"})
 */
class Feature
{
    public const TYPE_SCALAR = 1;
    public const TYPE_INT = 2;
    public const TYPE_REAL = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    public ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;

    /**
     * @ORM\Column(type="smallint")
     */
    public int $type;

    public function __construct(string $name, int $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
}
