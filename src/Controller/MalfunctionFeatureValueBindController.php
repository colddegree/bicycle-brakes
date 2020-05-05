<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\Malfunction;
use App\Entity\MalfunctionFeatureValueBind;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use App\IntervalMerger;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\RealIntervalsToStringMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MalfunctionFeatureValueBindController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $malfunctionRepository;
    private ObjectRepository $malfunctionFeatureValueBindRepository;
    private ObjectRepository $featureRepository;
    private IntervalMerger $intervalMerger;
    private IntIntervalsToStringMapper $intIntervalsToStringMapper;
    private RealIntervalsToStringMapper $realIntervalsToStringMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        IntervalMerger $intervalMerger,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper
    ) {
        $this->entityManager = $entityManager;
        $this->malfunctionRepository = $entityManager->getRepository(Malfunction::class);
        $this->malfunctionFeatureValueBindRepository = $entityManager->getRepository(MalfunctionFeatureValueBind::class);
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->intervalMerger = $intervalMerger;
        $this->intIntervalsToStringMapper = $intIntervalsToStringMapper;
        $this->realIntervalsToStringMapper = $realIntervalsToStringMapper;
    }

    /**
     * @Route("/malfunction-feature-value")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handlePost($request);
        }

        return $this->render('malfunction_feature_value_bind/index.html.twig', [
            'data' => json_encode($this->getData(),  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    private function handlePost(Request $request): void
    {
        // TODO: добавить валидацию

        $malfunctions = $request->request->get('malfunctions', []);

        if (empty($malfunctions)) {
            return;
        }

        foreach ($malfunctions as $mId => $m) {
            if (empty($m['features'])) {
                continue;
            }

            $mId = (int)$mId;

            /** @var Malfunction $malfunction */
            $malfunction = $this->malfunctionRepository->find($mId);

            foreach ($m['features'] as $featureType => $featureIdToValueDataMap) {
                foreach ($featureIdToValueDataMap as $featureId => $valueData) {
                    /** @var Feature $feature */
                    $feature = $this->featureRepository->find((int)$featureId);

                    /** @var MalfunctionFeatureValueBind $bind */
                    $bind = current($this->malfunctionFeatureValueBindRepository->findBy([
                        'malfunction' => $malfunction,
                        'feature' => $feature,
                    ]));

                    switch ($featureType) {
                        case 'scalar':
                            $this->persistScalarValues($valueData, $feature, $bind);
                            break;
                        case 'int':
                            $this->persistIntValues($valueData, $bind);
                            break;
                        case 'real':
                            $this->persistRealValues($valueData, $bind);
                            break;
                        default:
                            throw new RuntimeException(sprintf('Unsupported feature type "%s" in data', $featureType));
                    }
                }
            }
        }

        $this->entityManager->flush();
    }

    private function persistScalarValues(array $valueData, Feature $feature, MalfunctionFeatureValueBind $bind): void
    {
        $checkedIds = array_map('\intval', $valueData['selectedIds'] ?? []);

        foreach ($valueData['updatedIds'] as $id) {
            $id = (int)$id;
            if (in_array($id, $checkedIds, true)) {
                $scalarValueToAdd = $feature->possibleValues
                    ->filter(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->id === $id)
                    ->first()
                    ->scalarValue;
                $bind->scalarValues->add($scalarValueToAdd);
            } else {
                $scalarValueToRemove = $bind->scalarValues
                    ->filter(static fn(ScalarValue $v) => $v->id === $id)
                    ->first();
                $bind->scalarValues->removeElement($scalarValueToRemove);
            }
        }
    }

    private function persistIntValues(array $valueData, MalfunctionFeatureValueBind $bind): void
    {
        $updatedIds = [];
        if (!empty($valueData['updatedIds'])) {
            $updatedIds = array_map('\intval', explode(',', $valueData['updatedIds']));
        }
        $deletedIds = [];
        if (!empty($valueData['deletedIds'])) {
            $deletedIds = array_map('\intval', explode(',', $valueData['deletedIds']));
        }
        unset($valueData['updatedIds'], $valueData['deletedIds']);

        foreach ($valueData as $id => $data) {
            $id = (int)$id;

            if ($id <= 0) {
                // create
                $bind->intValues->add(new IntValue((int)$data['lower'], (int)$data['upper']));
            } elseif (in_array($id, $updatedIds, true)) {
                // update
                /** @var IntValue $intValue */
                $intValue = $bind->intValues->filter(static fn (IntValue $v) => $v->id === $id)->first();
                $intValue->lower = (int)$data['lower'];
                $intValue->upper = (int)$data['upper'];
            }
        }

        // delete
        foreach ($deletedIds as $id) {
            /** @var IntValue $intValue */
            $intValue = $bind->intValues->filter(static fn (IntValue $v) => $v->id === $id)->first();
            $bind->intValues->removeElement($intValue);
        }
    }

    private function persistRealValues(array $valueData, MalfunctionFeatureValueBind $bind): void
    {
        // копипаста persistIntValues)))))00

        $updatedIds = [];
        if (!empty($valueData['updatedIds'])) {
            $updatedIds = array_map('\intval', explode(',', $valueData['updatedIds']));
        }
        $deletedIds = [];
        if (!empty($valueData['deletedIds'])) {
            $deletedIds = array_map('\intval', explode(',', $valueData['deletedIds']));
        }
        unset($valueData['updatedIds'], $valueData['deletedIds']);

        foreach ($valueData as $id => $data) {
            $id = (int)$id;

            if ($id <= 0) {
                // create
                $bind->realValues->add(new RealValue(
                    (float)$data['lower'],
                    !empty($data['lowerIsInclusive']),
                    (float)$data['upper'],
                    !empty($data['upperIsInclusive']),
                ));
            } elseif (in_array($id, $updatedIds, true)) {
                // update
                /** @var RealValue $realValue */
                $realValue = $bind->realValues->filter(static fn (RealValue $v) => $v->id === $id)->first();
                $realValue->lower = (float)$data['lower'];
                $realValue->lowerIsInclusive = !empty($data['lowerIsInclusive']);
                $realValue->upper = (float)$data['upper'];
                $realValue->upperIsInclusive = !empty($data['upperIsInclusive']);
            }
        }

        // delete
        foreach ($deletedIds as $id) {
            /** @var IntValue $realValue */
            $realValue = $bind->realValues->filter(static fn (RealValue $v) => $v->id === $id)->first();
            $bind->realValues->removeElement($realValue);
        }
    }

    private function getData(): array
    {
        $result = [];

        /** @var Malfunction[] $malfunctions */
        $malfunctions = $this->malfunctionRepository->findAll();

        foreach ($malfunctions as $malfunction) {
            $malfunctionItem = [
                'id' => $malfunction->id,
                'name' => $malfunction->name,
                'features' => [],
            ];

            foreach ($malfunction->features as $feature) {
                /** @var MalfunctionFeatureValueBind $bind */
                $bind = current($this->malfunctionFeatureValueBindRepository->findBy([
                    'malfunction' => $malfunction,
                    'feature' => $feature,
                ]));

                [$values, $possibleScalarValues, $possibleValueDomain] = $bind->getValuesAsArray(
                    $this->intervalMerger,
                    $this->intIntervalsToStringMapper,
                    $this->realIntervalsToStringMapper,
                );

                $featureItem = $feature->toArray();
                $featureItem['values'] = $values;
                $featureItem['possibleScalarValues'] = $possibleScalarValues ?? [];
                $featureItem['possibleValueDomain'] = $possibleValueDomain;

                $malfunctionItem['features'][] = $featureItem;
            }

            $result['malfunctions'][] = $malfunctionItem;
        }

        return $result;
    }
}
