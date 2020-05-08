<?php

declare(strict_types=1);

namespace App\KnowledgeTree;

use App\Entity\Feature;
use App\Entity\FeatureNormalValue;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\RealValue;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\RealIntervalsToStringMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;

class KnowledgeTreeCreator
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $featureRepository;
    private IntIntervalsToStringMapper $intIntervalsToStringMapper;
    private RealIntervalsToStringMapper $realIntervalsToStringMapper;
    private SubsetValidator $subsetValidator;

    public function __construct(
        EntityManagerInterface $entityManager,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper,
        SubsetValidator $subsetValidator
    ) {
        $this->entityManager = $entityManager;
        $this->featureRepository = $this->entityManager->getRepository(Feature::class);
        $this->intIntervalsToStringMapper = $intIntervalsToStringMapper;
        $this->realIntervalsToStringMapper = $realIntervalsToStringMapper;
        $this->subsetValidator = $subsetValidator;
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
                $stringValues = $feature->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->value)
                    ->toArray();
                $summary = sprintf(
                    '{%s}',
                    implode(', ', array_map(static fn (string $v) => sprintf('"%s"', $v), $stringValues)),
                );
                break;

            case Feature::TYPE_INT:
                $valueCollection = $feature->possibleValues->map(static fn (FeaturePossibleValue $fpv) => $fpv->intValue);
                $summary = $this->intIntervalsToStringMapper->map($valueCollection->toArray());
                $stringValues = $valueCollection
                    ->map(fn (IntValue $v) => $this->intIntervalsToStringMapper->map([$v]))
                    ->toArray();
                break;

            case Feature::TYPE_REAL:
                $valueCollection = $feature->possibleValues->map(static fn (FeaturePossibleValue $fpv) => $fpv->realValue);
                $summary = $this->realIntervalsToStringMapper->map($valueCollection->toArray());
                $stringValues = $valueCollection
                    ->map(fn (RealValue $v) => $this->realIntervalsToStringMapper->map([$v]))
                    ->toArray();
                break;

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
        }

        return [$summary, $stringValues];
    }

    private function fetchNormalValuesSubtree(Feature $feature): array
    {
        switch ($feature->type) {
            case Feature::TYPE_SCALAR:
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

                $possibleValueStrings = $feature->possibleValues->map(
                    static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->value,
                )->toArray();
                $isSubsetOfPossibleValuesFlags = $stringValueCollection->map(
                    static fn (string $s) => in_array($s, $possibleValueStrings, true),
                )->toArray();
                break;


            case Feature::TYPE_INT:
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
            array_map(static fn (string $value, bool $flag) => [
                'value' => $value,
                'isSubsetOfPossibleValues' => $flag,
            ], $stringValues, $isSubsetOfPossibleValuesFlags),
        ];
    }

    private function fetchMalfunctionsSubtree(): array
    {
        return []; // TODO
    }
}
