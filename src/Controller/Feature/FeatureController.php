<?php

declare(strict_types=1);

namespace App\Controller\Feature;

use App\Entity\Feature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FeatureController extends AbstractController
{
    /**
     * @Route("/feature", name="feature")
     */
    public function index()
    {
        /** @var Feature[] $features */
        $features = $this->getDoctrine()->getRepository(Feature::class)
            ->findAll();

        $data = [];
        foreach ($features as $feature) {
            $data[] = [
                'id' => $feature->id,
                'name' => $feature->name,
                'type' => $feature->type,
            ];
        }

        return $this->render('feature/index.html.twig', [
            'data' => json_encode($data,  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }
}
