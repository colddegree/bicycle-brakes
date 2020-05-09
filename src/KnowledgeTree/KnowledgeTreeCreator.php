<?php

declare(strict_types=1);

namespace App\KnowledgeTree;

use App\Entity\Feature;
use App\Entity\FeatureNormalValue;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\Malfunction;
use App\Entity\MalfunctionFeatureValueBind;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\RealIntervalsToStringMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;

class KnowledgeTreeCreator
{
    private ObjectRepository $featureRepository;
    private IntIntervalsToStringMapper $intIntervalsToStringMapper;
    private RealIntervalsToStringMapper $realIntervalsToStringMapper;
    private SubsetValidator $subsetValidator;
    private ObjectRepository $malfunctionRepository;
    private ObjectRepository $malfunctionFeatureValueBindRepository;
    private IntersectValidator $intersectValidator;

    public function __construct(
        EntityManagerInterface $entityManager,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper,
        SubsetValidator $subsetValidator,
        IntersectValidator $intersectValidator
    ) {
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->intIntervalsToStringMapper = $intIntervalsToStringMapper;
        $this->realIntervalsToStringMapper = $realIntervalsToStringMapper;
        $this->subsetValidator = $subsetValidator;
        $this->malfunctionRepository = $entityManager->getRepository(Malfunction::class);
        $this->malfunctionFeatureValueBindRepository = $entityManager->getRepository(MalfunctionFeatureValueBind::class);
        $this->intersectValidator = $intersectValidator;
    }

    public function create(): array
    {
        return [
            'features' => $this->fetchFeatureSubtree(),
            'malfunctions' => $this->fetchMalfunctionsSubtree(),
        ];
    }

    private function fetchFeatureSubtree(): array
    {
        $tree = [];

        /** @var Feature[] $features */
        $features = $this->featureRepository->findAll();

        foreach ($features as $f) {
            [$possibleValuesSummary, $possibleValues] = $this->fetchFeaturePossibleValuesSubtree($f);
            [$normalValuesSummary, $normalValues] = $this->fetchNormalValuesSubtree($f);
            $tree[] = [
                'id' => $f->id,
                'name' => $f->name,
                'type' => $f->type,
                'possibleValues' => [
                    'summary' => $possibleValuesSummary,
                    'values' => $possibleValues,
                ],
                'normalValues' => [
                    'summary' => $normalValuesSummary,
                    'values' => $normalValues,
                ],
            ];
        }

        return $tree;
    }

    private function fetchFeaturePossibleValuesSubtree(Feature $feature): array
    {
        switch ($feature->type) {
            case Feature::TYPE_SCALAR:
                $ids = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->id)
                    ->toArray();

                $stringValues = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->value)
                    ->toArray();
                $summary = sprintf(
                    '{%s}',
                    implode(', ', array_map(static fn (string $v) => sprintf('"%s"', $v), $stringValues)),
                );
                break;

            case Feature::TYPE_INT:
                $ids = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->intValue->id)
                    ->toArray();

                $valueCollection = $feature->possibleValues->map(static fn (FeaturePossibleValue $fpv) => $fpv->intValue);
                $summary = $this->intIntervalsToStringMapper->map($valueCollection->toArray());
                $stringValues = $valueCollection
                    ->map(fn (IntValue $v) => $this->intIntervalsToStringMapper->map([$v]))
                    ->toArray();
                break;

            case Feature::TYPE_REAL:
                $ids = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->realValue->id)
                    ->toArray();

                $valueCollection = $feature->possibleValues->map(static fn (FeaturePossibleValue $fpv) => $fpv->realValue);
                $summary = $this->realIntervalsToStringMapper->map($valueCollection->toArray());
                $stringValues = $valueCollection
                    ->map(fn (RealValue $v) => $this->realIntervalsToStringMapper->map([$v]))
                    ->toArray();
                break;

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
        }

        return [
            $summary,
            array_map(static fn (int $id, string $value) => [
                'id' => $id,
                'value' => $value,
            ], $ids, $stringValues),
        ];
    }

    private function fetchNormalValuesSubtree(Feature $feature): array
    {
        switch ($feature->type) {
            case Feature::TYPE_SCALAR:
                $ids = $feature->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->scalarValue->id)
                    ->toArray();

                $stringValueCollection = $feature->normalValues->map(
                    static fn (FeatureNormalValue $fnv) => $fnv->scalarValue->value,
                );
                $summary = sprintf(
                    '{%s}',
                    implode(', ', $stringValueCollection->map(
                        static fn (string $s) => sprintf('"%s"', $s),
                    )->toArray()),
                );
                $stringValues = $stringValueCollection->toArray();

                $possibleValueStrings = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->value)
                    ->toArray();
                $isSubsetOfPossibleValuesFlags = $stringValueCollection
                    ->map(static fn (string $s) => in_array($s, $possibleValueStrings, true))
                    ->toArray();
                break;

            case Feature::TYPE_INT:
                $ids = $feature->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->intValue->id)
                    ->toArray();

                $valueCollection = $feature->normalValues->map(static fn (FeatureNormalValue $fnv) => $fnv->intValue);
                $summary = $this->intIntervalsToStringMapper->map($valueCollection->toArray());
                $stringValues = $valueCollection
                    ->map(fn (IntValue $v) => $this->intIntervalsToStringMapper->map([$v]))
                    ->toArray();

                $possibleValues = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->intValue)
                    ->toArray();
                $isSubsetOfPossibleValuesFlags = $valueCollection
                    ->map(fn (IntValue $v) => $this->subsetValidator->checkAsAreSubsetOfBsInt([$v], $possibleValues))
                    ->toArray();
                break;

            case Feature::TYPE_REAL:
                $ids = $feature->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->realValue->id)
                    ->toArray();

                $valueCollection = $feature->normalValues->map(static fn (FeatureNormalValue $fnv) => $fnv->realValue);
                $summary = $this->realIntervalsToStringMapper->map($valueCollection->toArray());
                $stringValues = $valueCollection
                    ->map(fn (RealValue $v) => $this->realIntervalsToStringMapper->map([$v]))
                    ->toArray();

                $possibleValues = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->realValue)
                    ->toArray();
                $isSubsetOfPossibleValuesFlags = $valueCollection
                    ->map(fn (RealValue $v) => $this->subsetValidator->checkAsAreSubsetOfBsReal([$v], $possibleValues))
                    ->toArray();
                break;

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
        }

        return [
            $summary,
            array_map(static fn (int $id, string $value, bool $flag) => [
                'id' => $id,
                'value' => $value,
                'isSubsetOfPossibleValues' => $flag,
            ], $ids, $stringValues, $isSubsetOfPossibleValuesFlags),
        ];
    }

    private function fetchMalfunctionsSubtree(): array
    {
        $tree = [];

        /** @var Malfunction[] $malfunctions */
        $malfunctions = $this->malfunctionRepository->findAll();

        foreach ($malfunctions as $m) {
            $tree[] = [
                'id' => $m->id,
                'name' => $m->name,
                'clinicalPicture' => $m->features->map(fn (Feature $f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'malfunctionFeatureValues' => $this->fetchMalfunctionFeatureValuesSubtree(
                        current($this->malfunctionFeatureValueBindRepository->findBy([
                            'malfunction' => $m,
                            'feature' => $f,
                        ]))),
                ])->toArray(),
            ];
        }

        return $tree;
    }

    private function fetchMalfunctionFeatureValuesSubtree(MalfunctionFeatureValueBind $bind): array
    {
        switch ($bind->feature->type) {
            case Feature::TYPE_SCALAR:
                $ids = $bind->scalarValues->map(static fn (ScalarValue $v) => $v->id)->toArray();

                $stringValueCollection = $bind->scalarValues->map(static fn (ScalarValue $v) => $v->value);
                $summary = sprintf(
                    '{%s}',
                    implode(', ', $stringValueCollection->map(
                        static fn (string $s) => sprintf('"%s"', $s),
                    )->toArray()),
                );
                $stringValues = $stringValueCollection->toArray();

                $possibleValueStrings = $bind->feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->value)
                    ->toArray();
                $isSubsetOfPossibleValuesFlags = $stringValueCollection
                    ->map(static fn (string $s) => in_array($s, $possibleValueStrings, true))
                    ->toArray();

                $normalValueStrings = $bind->feature->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->scalarValue->value)
                    ->toArray();
                $isIntersectsWithNormalValuesFlags = $stringValueCollection
                    ->map(static fn (string $s) => in_array($s, $normalValueStrings, true))
                    ->toArray();
                break;

            case Feature::TYPE_INT:
                $ids = $bind->intValues->map(static fn (IntValue $v) => $v->id)->toArray();

                $summary = $this->intIntervalsToStringMapper->map($bind->intValues->toArray());
                $stringValues = $bind->intValues
                    ->map(fn (IntValue $v) => $this->intIntervalsToStringMapper->map([$v]))
                    ->toArray();

                $possibleValues = $bind->feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->intValue)
                    ->toArray();
                $isSubsetOfPossibleValuesFlags = $bind->intValues
                    ->map(fn (IntValue $v) => $this->subsetValidator->checkAsAreSubsetOfBsInt([$v], $possibleValues))
                    ->toArray();

                $normalValues = $bind->feature->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->intValue)
                    ->toArray();
                $isIntersectsWithNormalValuesFlags = $bind->intValues
                    ->map(fn (IntValue $v) => $this->intersectValidator->checkAsIntersectsWithBsInt([$v], $normalValues))
                    ->toArray();
                break;

            case Feature::TYPE_REAL:
                $ids = $bind->realValues->map(static fn (RealValue $v) => $v->id)->toArray();

                $summary = $this->realIntervalsToStringMapper->map($bind->realValues->toArray());
                $stringValues = $bind->realValues
                    ->map(fn (RealValue $v) => $this->realIntervalsToStringMapper->map([$v]))
                    ->toArray();

                $possibleValues = $bind->feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->realValue)
                    ->toArray();
                $isSubsetOfPossibleValuesFlags = $bind->realValues
                    ->map(fn (RealValue $v) => $this->subsetValidator->checkAsAreSubsetOfBsReal([$v], $possibleValues))
                    ->toArray();

                $normalValues = $bind->feature->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->realValue)
                    ->toArray();
                $isIntersectsWithNormalValuesFlags = $bind->realValues
                    ->map(fn (RealValue $v) => $this->intersectValidator->checkAsIntersectsWithBsReal([$v], $normalValues))
                    ->toArray();
                break;

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $bind->feature->type));
        }

        return [
            'summary' => $summary,
            'values' => array_map(
                static fn (int $id, string $value, bool $subsetFlag, bool $intersectFlag) => [
                    'id' => $id,
                    'value' => $value,
                    'isSubsetOfPossibleValues' => $subsetFlag,
                    'isIntersectsWithNormalValues' => $intersectFlag,
                ],
                $ids,
                $stringValues,
                $isSubsetOfPossibleValuesFlags,
                $isIntersectsWithNormalValuesFlags,
            ),
        ];
    }
}
