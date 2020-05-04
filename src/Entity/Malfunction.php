<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Malfunction
{
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

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
