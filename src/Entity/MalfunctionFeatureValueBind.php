<?php

declare(strict_types=1);

namespace App\Entity;

use App\IntervalMerger;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\RealIntervalsToStringMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;

/**
 * @ORM\Entity()
 */
class MalfunctionFeatureValueBind
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    public ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Malfunction")
     * @ORM\JoinColumn(nullable=false)
     */
    public Malfunction $malfunction;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Feature")
     * @ORM\JoinColumn(nullable=false)
     */
    public Feature $feature;

    /**
     * @var Collection|ScalarValue[]
     * @ORM\ManyToMany(targetEntity="App\Entity\ScalarValue", cascade={"persist"})
     */
    public Collection $scalarValues;

    /**
     * @var Collection|IntValue[]
     * @ORM\ManyToMany(targetEntity="App\Entity\IntValue", cascade={"persist"})
     */
    public Collection $intValues;

    /**
     * @var Collection|RealValue[]
     * @ORM\ManyToMany(targetEntity="App\Entity\RealValue", cascade={"persist"})
     */
    public Collection $realValues;

    public function __construct(Malfunction $malfunction, Feature $feature)
    {
        $this->malfunction = $malfunction;
        $this->feature = $feature;
        $this->scalarValues = new ArrayCollection();
        $this->intValues = new ArrayCollection();
        $this->realValues = new ArrayCollection();
    }

    public function getValuesAsArray(
        IntervalMerger $intervalMerger,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper
    ): array {
        switch ($this->feature->type) {
            case Feature::TYPE_SCALAR:
                $possibleValues = $this->feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $pv) => [
                        'id' => $pv->scalarValue->id,
                        'value' => $pv->scalarValue->value,
                    ])
                    ->toArray();
                return [
                    array_values($this->scalarValues->map(static fn (ScalarValue $v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                    ])->toArray()),
                    $possibleValues,
                    null,
                    null,
                ];

            case Feature::TYPE_INT:
                $possibleValues = $this->feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $pv) => $pv->intValue)
                    ->toArray();
                $possibleValueDomainString = $intIntervalsToStringMapper->map($intervalMerger->mergeInt($possibleValues));
                $normalValues = $this->feature->normalValues
                    ->map(static fn (FeatureNormalValue $nv) => $nv->intValue)
                    ->toArray();
                $normalValueDomainString = $intIntervalsToStringMapper->map($intervalMerger->mergeInt($normalValues));
                return [
                    array_values($this->intValues->map(static fn (IntValue $v) => [
                        'id' => $v->id,
                        'lower' => $v->lower,
                        'upper' => $v->upper,
                    ])->toArray()),
                    null,
                    $possibleValueDomainString,
                    $normalValueDomainString,
                ];

            case Feature::TYPE_REAL:
                $possibleValues = $this->feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $pv) => $pv->realValue)
                    ->toArray();
                $possibleValueDomainString = $realIntervalsToStringMapper->map($intervalMerger->mergeReal($possibleValues));
                $normalValues = $this->feature->normalValues
                    ->map(static fn (FeatureNormalValue $nv) => $nv->realValue)
                    ->toArray();
                $normalValueDomainString = $realIntervalsToStringMapper->map($intervalMerger->mergeReal($normalValues));
                return [
                    array_values($this->realValues->map(static fn (RealValue $v) => [
                        'id' => $v->id,
                        'lower' => $v->lower,
                        'lowerIsInclusive' => $v->lowerIsInclusive,
                        'upper' => $v->upper,
                        'upperIsInclusive' => $v->upperIsInclusive,
                    ])->toArray()),
                    null,
                    $possibleValueDomainString,
                    $normalValueDomainString,
                ];

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $this->feature->type));
        }
    }
}
