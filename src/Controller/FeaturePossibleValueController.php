<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use App\Mapper\IntValueToArrayMapper;
use App\Mapper\RealValueToArrayMapper;
use App\Mapper\ScalarValueToArrayMapper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeaturePossibleValueController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $featureRepository;
    private ScalarValueToArrayMapper $scalarValueToArrayMapper;
    private IntValueToArrayMapper $intValueToArrayMapper;
    private RealValueToArrayMapper $realValueToArrayMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScalarValueToArrayMapper $scalarValueToArrayMapper,
        IntValueToArrayMapper $intValueToArrayMapper,
        RealValueToArrayMapper $realValueToArrayMapper
    ) {
        $this->entityManager = $entityManager;
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->scalarValueToArrayMapper = $scalarValueToArrayMapper;
        $this->intValueToArrayMapper = $intValueToArrayMapper;
        $this->realValueToArrayMapper = $realValueToArrayMapper;
    }

    /**
     * @Route("/feature-possible-values")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handlePost($request);
        }

        return $this->render('feature_possible_value/index.html.twig', [
            'data' => json_encode($this->getData(),  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
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
            $possibleValue = $this->createPossibleValueFromArray($feature, $arr);
            $this->entityManager->persist($possibleValue);
        }

        // update
        foreach ($updatedValueArrays as $arr) {
            /** @var Feature $feature */
            $feature = current(array_filter($features, static fn (Feature $f) => $f->id === $arr['featureId']));

            $valueId = $arr['valueId'];

            $existingValue = $feature->possibleValues->filter(static fn (FeaturePossibleValue $v) =>
                ($v->scalarValue !== null && $v->scalarValue->id === $valueId)
                || ($v->intValue !== null && $v->intValue->id === $valueId)
                || ($v->realValue !== null && $v->realValue->id === $valueId)
            )->first();

            $updatedValue = $this->updatePossibleValueFieldsFromArray($existingValue, $arr);
            $this->entityManager->persist($updatedValue);
        }

        $this->entityManager->flush();

        // delete
        foreach ($featureIdToDeleteValueIdsMap as $featureId => $valueIds) {
            if (empty($valueIds)) {
                continue;
            }
            /** @var Feature $feature */
            $feature = current(array_filter($features, static fn (Feature $f) => $f->id === $featureId));
            $this->deletePossibleValues($feature, $valueIds);
        }
    }

    private function createPossibleValueFromArray(Feature $feature, array $valueArr): FeaturePossibleValue
    {
        $possibleValue = new FeaturePossibleValue($feature);
        switch ($feature->type) {
            case Feature::TYPE_SCALAR:
                $possibleValue->scalarValue = new ScalarValue($valueArr['value']);
                break;
            case Feature::TYPE_INT:
                $possibleValue->intValue = new IntValue((int)$valueArr['lower'], (int)$valueArr['upper']);
                break;
            case Feature::TYPE_REAL:
                $possibleValue->realValue = new RealValue(
                    (float)$valueArr['lower'],
                    !empty($valueArr['lowerIsInclusive']),
                    (float)$valueArr['upper'],
                    !empty($valueArr['upperIsInclusive']),
                );
                break;
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
        }
        return $possibleValue;
    }

    private function updatePossibleValueFieldsFromArray(FeaturePossibleValue $value, array $valueArr): FeaturePossibleValue
    {
        switch ($value->feature->type) {
            case Feature::TYPE_SCALAR:
                $value->scalarValue->value = $valueArr['value'];
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
    private function deletePossibleValues(Feature $feature, array $valueIds): void
    {
        if (empty($valueIds)) {
            return;
        }

        $qb = $this->entityManager->getConnection()->createQueryBuilder();
        $qb
            ->delete('feature_possible_value')
            ->where($qb->expr()->eq('feature_id', ':featureId'))
            ->setParameter('featureId', $feature->id, ParameterType::INTEGER);

        switch ($feature->type) {
            case Feature::TYPE_SCALAR:
                $valueColumnName = 'scalar_value_id';
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
            $possibleValues = [];
            foreach ($feature->possibleValues as $possibleValue) {
                switch ($feature->type) {
                    case Feature::TYPE_SCALAR:
                        $possibleValues[] = $this->scalarValueToArrayMapper->map($possibleValue->scalarValue);
                        break;
                    case Feature::TYPE_INT:
                        $possibleValues[] = $this->intValueToArrayMapper->map($possibleValue->intValue);
                        break;
                    case Feature::TYPE_REAL:
                        $possibleValues[] = $this->realValueToArrayMapper->map($possibleValue->realValue);
                        break;
                    default:
                        throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
                }
            }
            $item['possibleValues'] = $possibleValues;
            $result[] = $item;
        }

        return $result;
    }
}
