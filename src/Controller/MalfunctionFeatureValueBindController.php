<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeaturePossibleValue;
use App\Entity\Malfunction;
use App\Entity\MalfunctionFeatureValueBind;
use App\Entity\ScalarValue;
use App\IntervalMerger;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\RealIntervalsToStringMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
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

        $this->persistScalarValues($request);
        $this->persistIntAndRealValues($request);
        $this->entityManager->flush();
    }

    private function persistScalarValues(Request $request): void
    {
        $malfunctions = $request->request->get('malfunctions', []);

        if (empty($malfunctions)) {
            return;
        }

        foreach ($malfunctions as $m) {
            /** @var Malfunction $malfunction */
            $malfunction = $this->malfunctionRepository->find((int)$m['id']);

            foreach ($m['features']['scalar'] as $f) {
                /** @var Feature $feature */
                $feature = $this->featureRepository->find((int)$f['id']);

                /** @var MalfunctionFeatureValueBind $bind */
                $bind = current($this->malfunctionFeatureValueBindRepository->findBy([
                    'malfunction' => $malfunction,
                    'feature' => $feature,
                ]));

                $checkedIds = array_map('\intval', $f['selectedIds']);

                foreach ($f['updatedIds'] as $id) {
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
        }
    }

    private function persistIntAndRealValues(Request $request): void
    {
        // TODO
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
                if (!empty($possibleScalarValues)) {
                    $featureItem['possibleScalarValues'] = $possibleScalarValues;
                }
                if (!empty($possibleValueDomain)) {
                    $featureItem['possibleValueDomain'] = $possibleValueDomain;
                }

                $malfunctionItem['features'][] = $featureItem;
            }

            $result['malfunctions'][] = $malfunctionItem;
        }

        return $result;
    }
}
