<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FeatureRepository")
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
    public ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public string $name;

    /**
     * @ORM\Column(type="smallint")
     */
    public int $type;

    /**
     * @var Collection|FeaturePossibleValue[]
     * @ORM\OneToMany(targetEntity="App\Entity\FeaturePossibleValue", mappedBy="feature")
     */
    public Collection $possibleValues;

    /**
     * @var Collection|FeatureNormalValue[]
     * @ORM\OneToMany(targetEntity="App\Entity\FeatureNormalValue", mappedBy="feature")
     */
    public Collection $normalValues;

    public function __construct(string $name, int $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->possibleValues = new ArrayCollection();
        $this->normalValues = new ArrayCollection();
    }

    public static function fromArray(array $arr): self
    {
        return new self($arr['name'], (int)$arr['type']);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
