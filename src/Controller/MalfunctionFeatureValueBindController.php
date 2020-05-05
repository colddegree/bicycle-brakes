<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Malfunction;
use App\Entity\MalfunctionFeatureValueBind;
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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->malfunctionRepository = $entityManager->getRepository(Malfunction::class);
        $this->malfunctionFeatureValueBindRepository = $entityManager->getRepository(MalfunctionFeatureValueBind::class);
    }

    /**
     * @Route("/malfunction-feature-value")
     */
    public function index(Request $request): Response
    {
        return $this->render('malfunction_feature_value_bind/index.html.twig', [
            'data' => json_encode($this->getData(),  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
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

                $featureItem = $feature->toArray();
                $featureItem['values'] = $bind->getValuesAsArray();

                $malfunctionItem['features'][] = $featureItem;
            }

            $result['malfunctions'][] = $malfunctionItem;
        }

        return $result;
    }
}
