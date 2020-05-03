<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class FeaturePossibleValue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    public ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Feature", inversedBy="possibleValues")
     * @ORM\JoinColumn(nullable=false)
     */
    public Feature $feature;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ScalarValue", cascade={"persist"})
     */
    public ?ScalarValue $scalarValue = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\IntValue", cascade={"persist"})
     */
    public ?IntValue $intValue = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RealValue", cascade={"persist"})
     */
    public ?RealValue $realValue = null;

    public function __construct(Feature $feature)
    {
        $this->feature = $feature;
    }
}
