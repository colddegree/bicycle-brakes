<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\Malfunction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClinicalPictureController extends AbstractReactController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $malfunctionRepository;
    private ObjectRepository $featureRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->malfunctionRepository = $this->entityManager->getRepository(Malfunction::class);
        $this->featureRepository = $this->entityManager->getRepository(Feature::class);
    }

    /**
     * @Route("/клинические-картины", name="клинические-картины")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handlePost($request);
        }

        $name = 'Клинические картины';
        return $this->renderPageWithReact(
            $name,
            $name,
            $this->getData(),
            'clinical-picture',
        );
    }

    private function handlePost(Request $request): void
    {
        // TODO: добавить валидацию

        $malfunctions = $request->request->get('malfunctions', []);
        $malfunctions = array_filter($malfunctions, static fn (array $data) => !empty($data['updatedIds']));

        $updatedMalfunctionIds = array_map(static fn (array $data) => (int)$data['id'], $malfunctions);

        $malfunctionEntities = $this->malfunctionRepository->findBy(['id' => $updatedMalfunctionIds]);

        /** @var Malfunction[] $malfunctionIdToEntityMap */
        $malfunctionIdToEntityMap = array_reduce(
            $malfunctionEntities,
            static function (array $acc, Malfunction $m) {
                $acc[$m->id] = $m;
                return $acc;
            },
            [],
        );

        foreach ($updatedMalfunctionIds as $id) {
            $updatedFeatureIds = array_map('\intval', $malfunctions[$id]['updatedIds']);
            $selectedFeatureIds = array_map('\intval', $malfunctions[$id]['selectedFeatureIds'] ?? []);

            foreach ($updatedFeatureIds as $featureId) {
                $malfunctionToUpdate = $malfunctionIdToEntityMap[$id];

                if (in_array($featureId, $selectedFeatureIds, true)) {
                    // добавить к неисправности признак
                    $feature = $this->featureRepository->find($featureId);
                    $malfunctionToUpdate->features->add($feature);
                } else {
                    // удалить у неисправности признак
                    /** @var Feature $feature */
                    $feature = $malfunctionToUpdate->features->filter(static fn (Feature $f) => $f->id === $featureId)->first();
                    $malfunctionToUpdate->features->removeElement($feature);
                }
            }
        }

        $this->entityManager->flush();
    }

    private function getData(): array
    {
        /** @var Malfunction[] $malfunctions */
        $malfunctions = $this->malfunctionRepository->findAll();

        $result = [
            'malfunctions' => [],
            'allFeatures' => [],
        ];

        foreach ($malfunctions as $malfunction) {
            $item = [
                'id' => $malfunction->id,
                'name' => $malfunction->name,
                'selectedFeatureIds' => [],
            ];

            /** @var Feature[] $selectedFeatures */
            $selectedFeatures = $malfunction->features->toArray();

            foreach ($selectedFeatures as $feature) {
                $item['selectedFeatureIds'][] = $feature->id;
            }

            $result['malfunctions'][] = $item;
        }

        /** @var Feature[] $allFeatures */
        $allFeatures = $this->featureRepository->findAll();

        foreach ($allFeatures as $feature) {
            $result['allFeatures'][] = [
                'id' => $feature->id,
                'name' => $feature->name,
            ];
        }

        return $result;
    }
}
