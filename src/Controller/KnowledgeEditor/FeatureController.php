<?php

declare(strict_types=1);

namespace App\Controller\KnowledgeEditor;

use App\Controller\AbstractReactController;
use App\Entity\Feature;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeatureController extends AbstractReactController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $featureRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->featureRepository = $entityManager->getRepository(Feature::class);
    }

    /**
     * @Route("/редактор-знаний/признаки", name="редактор-знаний:признаки")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handlePost($request);
        }

        /** @var Feature[] $features */
        $features = $this->featureRepository->findAll();

        $data = [];
        foreach ($features as $feature) {
            $data[] = $feature->toArray();
        }

        $name = 'Признаки';
        return $this->renderPageWithReact(
            $name,
            $name,
            $data,
            'feature',
        );
    }

    private function handlePost(Request $request): void
    {
        $updatedIds = array_map('\intval', explode(',', $request->request->get('updatedIds', '')));
        $deletedIds = array_map('\intval', explode(',', $request->request->get('deletedIds', '')));

        $newFeatures = [];
        $updatedFeatureArrays = [];
        foreach ($request->request->get('features', []) as $featureArr) {
            $id = (int)$featureArr['id'];
            if ($id <= 0) {
                $newFeatures[] = Feature::fromArray($featureArr);
            } elseif (in_array($id, $updatedIds, true)) {
                $updatedFeatureArrays[$id] = $featureArr;
            }
        }

        // create
        foreach ($newFeatures as $feature) {
            $this->entityManager->persist($feature);
        }

        // update
        /** @var Feature[] $updatedFeatures */
        $updatedFeatures = $this->featureRepository->findBy(['id' => array_keys($updatedFeatureArrays)]);

        foreach ($updatedFeatures as $feature) {
            $feature->name = $updatedFeatureArrays[$feature->id]['name'];
            $feature->type = (int)$updatedFeatureArrays[$feature->id]['type'];
            $this->entityManager->persist($feature);
        }

        $this->entityManager->flush();

        // delete
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->delete(Feature::class, 'f')
            ->where($qb->expr()->in('f.id', ':deletedIds'))
            ->setParameter(':deletedIds', $deletedIds)
            ->getQuery()
            ->execute();
    }
}
