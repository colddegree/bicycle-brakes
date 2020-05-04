<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    public ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;

    /**
     * @var Collection|Feature[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Feature")
     * @ORM\JoinTable(
     *     name="malfunction_clinical_picture",
     *     joinColumns={@ORM\JoinColumn(name="malfunction_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="feature_id", referencedColumnName="id")},
     * )
     */
    public Collection $features;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->features = new ArrayCollection();
    }
}
