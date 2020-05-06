<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeatureNormalValue;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use App\IntervalMerger;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\IntValueToArrayMapper;
use App\Mapper\RealIntervalsToStringMapper;
use App\Mapper\RealValueToArrayMapper;
use App\Mapper\ScalarValueToArrayMapper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeatureNormalValueController extends AbstractReactController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $featureRepository;
    private ScalarValueToArrayMapper $scalarValueToArrayMapper;
    private IntValueToArrayMapper $intValueToArrayMapper;
    private RealValueToArrayMapper $realValueToArrayMapper;
    private IntervalMerger $intervalMerger;
    private IntIntervalsToStringMapper $intIntervalsToStringMapper;
    private RealIntervalsToStringMapper $realIntervalsToStringMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScalarValueToArrayMapper $scalarValueToArrayMapper,
        IntValueToArrayMapper $intValueToArrayMapper,
        RealValueToArrayMapper $realValueToArrayMapper,
        IntervalMerger $intervalMerger,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper
    ) {
        $this->entityManager = $entityManager;
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->scalarValueToArrayMapper = $scalarValueToArrayMapper;
        $this->intValueToArrayMapper = $intValueToArrayMapper;
        $this->realValueToArrayMapper = $realValueToArrayMapper;
        $this->intervalMerger = $intervalMerger;
        $this->intIntervalsToStringMapper = $intIntervalsToStringMapper;
        $this->realIntervalsToStringMapper = $realIntervalsToStringMapper;
    }

    /**
     * @Route("/нормальные-значения-признаков", name="нормальные-значения-признаков")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handlePost($request);
        }

        $name = 'Нормальные значения признаков';
        return $this->renderPageWithReact(
            $name,
            $name,
            $this->getData(),
            'feature-normal-value',
        );
    }

    private function handlePost(Request $request): void
    {
        // TODO: добавить валидацию

        $newValueArrays = [];
        $updatedValueArrays = [];

        $featureIdToDeleteValueIdsMap = [];

        $featureIdToValuesMap = $request->request->get('values', []);

        foreach ($featureIdToValuesMap as $featureId => $values) {
            $featureId = (int)$featureId;


            $updatedIds = [];
            if (!empty($values['updatedIds'])) {
                $updatedIds = array_map('\intval', explode(',', $values['updatedIds']));
            }

            $deletedIds = [];
            if (!empty($values['deletedIds'])) {
                $deletedIds = array_map('\intval', explode(',', $values['deletedIds']));
            }

            unset($values['updatedIds'], $values['deletedIds']);


            $featureIdToDeleteValueIdsMap[$featureId] = $deletedIds;

            foreach ($values as $valueId => $valueArr) {
                $valueId = (int)$valueId;
                if ($valueId <= 0) {
                    $valueArr['featureId'] = $featureId;
                    $newValueArrays[] = $valueArr;
                } elseif (in_array($valueId, $updatedIds, true)) {
                    $valueArr['featureId'] = $featureId;
                    $valueArr['valueId'] = $valueId;
                    $updatedValueArrays[] = $valueArr;
                }
            }
        }

        $features = $this->featureRepository->findBy(['id' => array_keys($featureIdToValuesMap)]);

        // create
        foreach ($newValueArrays as $arr) {
            /** @var Feature $feature */
            $feature = current(array_filter($features, static fn (Feature $f) => $f->id === $arr['featureId']));
            $normalValue = $this->createNormalValueFromArray($feature, $arr);
            $this->entityManager->persist($normalValue);
        }

        // update
        foreach ($updatedValueArrays as $arr) {
            /** @var Feature $feature */
            $feature = current(array_filter($features, static fn (Feature $f) => $f->id === $arr['featureId']));

            $valueId = $arr['valueId'];

            // если это не скалярное значение, то используем скопипащенный функционал
            if (!isset($arr['checked'])) {
                $existingValue = $feature->normalValues->filter(static fn (FeatureNormalValue $v) =>
                    ($v->scalarValue !== null && $v->scalarValue->id === $valueId)
                    || ($v->intValue !== null && $v->intValue->id === $valueId)
                    || ($v->realValue !== null && $v->realValue->id === $valueId)
                )->first();

                $updatedValue = $this->updateNormalValueFieldsFromArray($existingValue, $arr);
                $this->entityManager->persist($updatedValue);

            // иначе, прикрутить костыли
            } else {
                $checked = $arr['checked'] === 'true';

                // значения не было
                if ($checked) {
                    // ищем скалярное значение по featureId и valueId и создаём FeatureNormalValue c этим значением

                    /** @var ScalarValue $existingScalarValue */
                    $existingScalarValue = $feature->possibleValues->filter(static fn (FeaturePossibleValue $v) =>
                        $v->scalarValue !== null && $v->scalarValue->id === $valueId
                    )->first()->scalarValue;

                    $newValue = new FeatureNormalValue($feature);
                    $newValue->scalarValue = $existingScalarValue;
                    $this->entityManager->persist($newValue);

                // значение было
                } else {
                    // ищем существующий FeatureNormalValue и удаляем
                    $existingValue = $feature->normalValues->filter(static fn (FeatureNormalValue $v) =>
                        $v->scalarValue !== null && $v->scalarValue->id === $valueId
                    )->first();
                    $this->entityManager->remove($existingValue);
                }
            }
        }

        $this->entityManager->flush();

        // delete
        foreach ($featureIdToDeleteValueIdsMap as $featureId => $valueIds) {
            if (empty($valueIds)) {
                continue;
            }
            /** @var Feature $feature */
            $feature = current(array_filter($features, static fn (Feature $f) => $f->id === $featureId));
            $this->deleteNormalValues($feature, $valueIds);
        }
    }

    private function createNormalValueFromArray(Feature $feature, array $valueArr): FeatureNormalValue
    {
        $normalValue = new FeatureNormalValue($feature);
        switch ($feature->type) {
            case Feature::TYPE_SCALAR:
                throw new RuntimeException('Скалярные значения здесь не создаются');
            case Feature::TYPE_INT:
                $normalValue->intValue = new IntValue((int)$valueArr['lower'], (int)$valueArr['upper']);
                break;
            case Feature::TYPE_REAL:
                $normalValue->realValue = new RealValue(
                    (float)$valueArr['lower'],
                    !empty($valueArr['lowerIsInclusive']),
                    (float)$valueArr['upper'],
                    !empty($valueArr['upperIsInclusive']),
                );
                break;
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
        }
        return $normalValue;
    }

    private function updateNormalValueFieldsFromArray(FeatureNormalValue $value, array $valueArr): FeatureNormalValue
    {
        switch ($value->feature->type) {
            case Feature::TYPE_SCALAR:
                return $value; // ничего не делаем, потом обработаем
                break;
            case Feature::TYPE_INT:
                $value->intValue->lower = (int)$valueArr['lower'];
                $value->intValue->upper = (int)$valueArr['upper'];
                break;
            case Feature::TYPE_REAL:
                $value->realValue->lower = (float)$valueArr['lower'];
                $value->realValue->lowerIsInclusive = !empty($valueArr['lowerIsInclusive']);
                $value->realValue->upper = (float)$valueArr['upper'];
                $value->realValue->upperIsInclusive = !empty($valueArr['upperIsInclusive']);
                break;
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $value->feature->type));
        }
        return $value;
    }

    /**
     * @param int[] $valueIds
     * @param Feature $feature
     */
    private function deleteNormalValues(Feature $feature, array $valueIds): void
    {
        if (empty($valueIds)) {
            return;
        }

        $qb = $this->entityManager->getConnection()->createQueryBuilder();
        $qb
            ->delete('feature_normal_value')
            ->where($qb->expr()->eq('feature_id', ':featureId'))
            ->setParameter('featureId', $feature->id, ParameterType::INTEGER);

        switch ($feature->type) {
            case Feature::TYPE_SCALAR:
                throw new RuntimeException('Скалярные значения здесь не удаляются');
                break;
            case Feature::TYPE_INT:
                $valueColumnName = 'int_value_id';
                break;
            case Feature::TYPE_REAL:
                $valueColumnName = 'real_value_id';
                break;
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
        }

        $qb
            ->andWhere($qb->expr()->in($valueColumnName, ':valueIds'))
            ->setParameter('valueIds', $valueIds, Connection::PARAM_INT_ARRAY)
            ->execute();
    }

    private function getData(): array
    {
        // TODO: доставать данные оптимальнее

        /** @var Feature[] $features */
        $features = $this->featureRepository->findAll();

        $result = [];
        foreach ($features as $feature) {
            $item = $feature->toArray();
            $item['possibleValues'] = $this->mapPossibleValuesToArray($feature->type, $feature->possibleValues->toArray());
            $item['normalValues'] = $this->mapNormalValuesToArray($feature->type, $feature->normalValues->toArray());
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param int $type {@see Feature types}
     * @param FeaturePossibleValue[] $values
     *
     * @return array
     */
    private function mapPossibleValuesToArray(int $type, array $values): array
    {
        switch ($type) {
            case Feature::TYPE_SCALAR:
                return array_map(fn (FeaturePossibleValue $v) => $this->scalarValueToArrayMapper->map($v->scalarValue), $values);
            case Feature::TYPE_INT:
                return (array)$this->intIntervalsToStringMapper->map(
                    $this->intervalMerger->mergeInt(
                        array_map(static fn (FeaturePossibleValue $v) => $v->intValue, $values),
                    ),
                );
            case Feature::TYPE_REAL:
                return (array)$this->realIntervalsToStringMapper->map(
                    $this->intervalMerger->mergeReal(
                        array_map(static fn (FeaturePossibleValue $v) => $v->realValue, $values),
                    ),
                );
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $type));
        }
    }

    /**
     * @param int $type {@see Feature types}
     * @param FeatureNormalValue[] $values
     *
     * @return array
     */
    private function mapNormalValuesToArray(int $type, array $values): array
    {
        $result = [];

        foreach ($values as $value) {
            switch ($type) {
                case Feature::TYPE_SCALAR:
                    $result[] = $this->scalarValueToArrayMapper->map($value->scalarValue);
                    break;
                case Feature::TYPE_INT:
                    $result[] = $this->intValueToArrayMapper->map($value->intValue);
                    break;
                case Feature::TYPE_REAL:
                    $result[] = $this->realValueToArrayMapper->map($value->realValue);
                    break;
                default:
                    throw new RuntimeException(sprintf('Unsupported type "%s"', $type));
            }
        }

        return $result;
    }
}
