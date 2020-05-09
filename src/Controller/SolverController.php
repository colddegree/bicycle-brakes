<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeaturePossibleValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SolverController extends AbstractReactController
{
    private ObjectRepository $featureRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->featureRepository = $entityManager->getRepository(Feature::class);
    }

    /**
     * @Route("/решатель-задач", name="решатель-задач")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            dump($request->request->all());//TODO
        }

        $name = 'Решатель задач';
        return $this->renderPageWithReact(
            $name,
            $name,
            $this->getData(),
            'solver',
            true,
        );
    }

    private function getData(): array
    {
        return [
            'features' => array_map(static fn (Feature $f) => [
                'id' => $f->id,
                'name' => $f->name,
                'type' => $f->type,
                'possibleScalarValues' => $f->type !== Feature::TYPE_SCALAR
                    ? null
                    : $f->possibleValues->map(static fn (FeaturePossibleValue $fpv) => [
                        'id' => $fpv->scalarValue->id,
                        'value' => $fpv->scalarValue->value,
                    ])->toArray(),
            ], $this->featureRepository->findAll()),
        ];
    }
}
