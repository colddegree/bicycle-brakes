<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\Malfunction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClinicalPictureController extends AbstractController
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
     * @Route("/clinical-pictures")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            dump($request->request->all());//TODO
        }

        return $this->render('clinical_picture/index.html.twig', [
            'data' => json_encode($this->getData(),  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    private function getData(): array
    {
        /** @var Malfunction[] $malfunctions */
        $malfunctions = $this->malfunctionRepository->findAll();

        $result = [];

        foreach ($malfunctions as $malfunction) {
            $item = [
                'id' => $malfunction->id,
                'name' => $malfunction->name,
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
