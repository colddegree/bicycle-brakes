<?php

declare(strict_types=1);

namespace App\Solver;

use App\Entity\Feature;
use App\Entity\FeatureNormalValue;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\Malfunction;
use App\Entity\MalfunctionFeatureValueBind;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use App\KnowledgeTree\SubsetValidator;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\RealIntervalsToStringMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;

class Solver
{
    private ObjectRepository $featureRepository;
    private SubsetValidator $subsetValidator;
    private ObjectRepository $malfunctionRepository;
    private IntIntervalsToStringMapper $intIntervalsToStringMapper;
    private RealIntervalsToStringMapper $realIntervalsToStringMapper;

    private ObjectRepository $malfunctionFeatureValueBindRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubsetValidator $subsetValidator,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper
    ) {
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->subsetValidator = $subsetValidator;
        $this->malfunctionRepository = $entityManager->getRepository(Malfunction::class);
        $this->intIntervalsToStringMapper = $intIntervalsToStringMapper;
        $this->realIntervalsToStringMapper = $realIntervalsToStringMapper;

        $this->malfunctionFeatureValueBindRepository = $entityManager->getRepository(MalfunctionFeatureValueBind::class);
    }

    /**
     * @param FeatureDto ...$dtos
     *
     * @return string[] сообщения
     */
    public function solve(FeatureDto ...$dtos): array
    {
        /** @var FeatureDto[] $idToDtoMap */
        $idToDtoMap = [];
        foreach ($dtos as $dto) {
            $idToDtoMap[$dto->featureId] = $dto;
        }

        /** @var Feature[] $features */
        $features = $this->featureRepository->findBy(['id' => array_keys($idToDtoMap)]);

        // 1. Проверяется гипотеза, что велосипед исправен, т.е. проверяется, что для каждого заданного признака наблюдаемое значение является нормальным значением этого признака.
        $allFeatureValuesAreNormal = array_reduce(
            $features,
            fn (bool $acc, Feature $f) => $acc = ($acc && $this->featureValueIsNormal($f, $idToDtoMap[$f->id])),
            true,
        );

        if ($allFeatureValuesAreNormal) {
            return ['Тормозная система велосипеда исправна, так как все заданные значения признаков являются нормальными.'];
        }

        $disprovedMalfunctionIds = [];

        // 2. Если гипотеза подзадачи 1 опровергнута, то для каждой неисправности m проверяется гипотеза о том, что велосипед имеет неисправность m. Эта подзадача может быть разбита на следующие подзадачи.
        // 2.1. Для каждого признака, не принадлежащего клинической картине неисправности m, проверяется гипотеза о том, что этот признак имеет только нормальные значения. Если по крайней мере для одного признака эта гипотеза опровергнута, то и гипотеза подзадачи 2 также опровергнута.

        /** @var Malfunction[] $malfunctions */
        $malfunctions = $this->malfunctionRepository->findAll();

        $messages = [];

        foreach ($malfunctions as $m) {
            $excludeFeatureIds = $m->features->map(static fn (Feature $f) => $f->id)->toArray();
            /** @var Feature[] $suspiciousFeatures */
            $suspiciousFeatures = array_filter($features, static fn (Feature $f) => !in_array($f->id, $excludeFeatureIds, true));

            /** @var Feature|null $contradictionFeature */
            $contradictionFeature = null;
            foreach ($suspiciousFeatures as $f) {
                if (!$this->featureValueIsNormal($f, $idToDtoMap[$f->id])) {
                    $contradictionFeature = $f;
                    $disprovedMalfunctionIds[] = $m->id;
                    break;
                }
            }

            if ($contradictionFeature !== null) {
                $messages[] = sprintf(
                    'Неисправность "%s" опровергнута, так как значение "%s" признака "%s" (не из клинической картины неисправности) не является нормальным.',
                    $m->name,
                    $this->mapValueToString($contradictionFeature, $idToDtoMap[$contradictionFeature->id]),
                    $contradictionFeature->name,
                );
            }
        }

        if (!empty($messages)) {
            $messages = $this->addPossibleMalfunctionMessage($messages, $malfunctions, $disprovedMalfunctionIds);
            return $messages;
        }

        // 2.2. Если ни одна из гипотез подзадачи 2.1 не опровергнута, то для каждого заданного признака f, принадлежащего клинической картине неисправности m, проверяется гипотеза о том, что все наблюдаемые значения признака f, согласуются с описанием клинического проявления для этого признака и неисправности m. Если ни одна из этих гипотез не опровергнута, то и гипотеза подзадачи 2 также не опровергнута.

        foreach ($malfunctions as $m) {
            foreach ($m->features->filter(static fn (Feature $f) => array_key_exists($f->id, $idToDtoMap)) as $f) {
                if (!$this->check($m, $f, $idToDtoMap[$f->id])) {
                    $messages[] = sprintf(
                        'Неисправность "%s" опровергнута, так как значение "%s" признака "%s" не соответствует описанию неисправности.',
                        $m->name,
                        $this->mapValueToString($f, $idToDtoMap[$f->id]),
                        $f->name,
                    );
                    $disprovedMalfunctionIds[] = $m->id;
                    break;
                }
            }
        }

        $messages = $this->addPossibleMalfunctionMessage($messages, $malfunctions, $disprovedMalfunctionIds);
        return $messages;
    }

    /**
     * @param string[] $messages
     * @param Malfunction[] $malfunctions
     * @param int[] $disprovedMalfunctionIds
     *
     * @return string[]
     */
    private function addPossibleMalfunctionMessage(
        array $messages,
        array $malfunctions,
        array $disprovedMalfunctionIds
    ): array {
        /** @var Malfunction[] $possibleMalfunctions */
        $possibleMalfunctions = array_filter(
            $malfunctions,
            static fn (Malfunction $m) => !in_array($m->id, $disprovedMalfunctionIds, true),
        );

        $messages[] = '';

        if (count($possibleMalfunctions) === 1) {
            $messages[] = sprintf('Выявленная неисправность: "%s".', reset($possibleMalfunctions)->name);
        } else {
            $quotedNames = array_map(static fn (Malfunction $m) => sprintf('"%s"', $m->name), $possibleMalfunctions);
            $messages[] = sprintf('Возможные неисправности: %s.', implode(', ', $quotedNames));
        }

        return $messages;
    }

    private function featureValueIsNormal(Feature $f, FeatureDto $dto): bool
    {
        switch ($f->type) {
            case Feature::TYPE_SCALAR:
                $value = $f->possibleValues
                    ->filter(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->id === (int)$dto->value)
                    ->first()
                    ->scalarValue
                    ->value;
                $normalValues = $f->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->scalarValue->value)
                    ->toArray();
                return in_array($value, $normalValues, true);

            case Feature::TYPE_INT:
                $value = (int)$dto->value;
                $normalValues = $f->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->intValue)
                    ->toArray();
                return $this->subsetValidator->checkAsAreSubsetOfBsInt([new IntValue($value, $value)], $normalValues);

            case Feature::TYPE_REAL:
                $value = (float)$dto->value;
                $normalValues = $f->normalValues
                    ->map(static fn (FeatureNormalValue $fnv) => $fnv->realValue)
                    ->toArray();
                return $this->subsetValidator->checkAsAreSubsetOfBsReal(
                    [new RealValue($value, true, $value, true)],
                    $normalValues,
                );

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $f->type));
        }
    }

    private function mapValueToString(Feature $f, FeatureDto $dto): string
    {
        switch ($f->type) {
            case Feature::TYPE_SCALAR:
                return $f->possibleValues
                    ->filter(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->id === (int)$dto->value)
                    ->first()
                    ->scalarValue
                    ->value;

            case Feature::TYPE_INT:
                $value = (int)$dto->value;
                $str = $this->intIntervalsToStringMapper->map([new IntValue($value, $value)]);
                return substr($str, 1, -1); // вырезать фигурные скобки

            case Feature::TYPE_REAL:
                $value = (float)$dto->value;
                $str = $this->realIntervalsToStringMapper->map([new RealValue($value, true, $value, true)]);
                return substr($str, 1, -1); // вырезать фигурные скобки

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $f->type));
        }
    }

    // TODO: переименовать (согласуется)
    private function check(Malfunction $m, Feature $f, FeatureDto $dto): bool
    {
        /** @var MalfunctionFeatureValueBind $bind */
        $bind = $this->malfunctionFeatureValueBindRepository->findBy([
            'malfunction' => $m,
            'feature' => $f,
        ])[0];

        switch ($f->type) {
            case Feature::TYPE_SCALAR:
                $value = $f->possibleValues
                    ->filter(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->id === (int)$dto->value)
                    ->first()
                    ->scalarValue
                    ->value;
                $values = $bind->scalarValues->map(static fn (ScalarValue $v) => $v->value)->toArray();
                return in_array($value, $values, true);

            case Feature::TYPE_INT:
                $value = (int)$dto->value;
                return $this->subsetValidator->checkAsAreSubsetOfBsInt(
                    [new IntValue($value, $value)],
                    $bind->intValues->toArray(),
                );

            case Feature::TYPE_REAL:
                $value = (float)$dto->value;
                return $this->subsetValidator->checkAsAreSubsetOfBsReal(
                    [new RealValue($value, true, $value, true)],
                    $bind->realValues->toArray(),
                );

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $f->type));
        }
    }
}
