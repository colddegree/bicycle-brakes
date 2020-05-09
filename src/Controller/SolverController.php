<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeaturePossibleValue;
use App\Solver\FeatureDto;
use App\Solver\Solver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SolverController extends AbstractReactController
{
    private ObjectRepository $featureRepository;
    private Solver $solver;

    public function __construct(EntityManagerInterface $entityManager, Solver $solver)
    {
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->solver = $solver;
    }

    /**
     * @Route("/решатель-задач", name="решатель-задач")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handlePost($request);
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

    private function handlePost(Request $request): void
    {
        // TODO: провалидировать, что пришедшие значения входят в соответствующие возможные значения
        // если не входит, то вернуть ошибку

        $featureIdToValueMap = $request->request->all();

        dump($featureIdToValueMap); // TODO: remove

        $messages = $this->solver->solve(...array_map(
            static fn ($k, $v) => new FeatureDto((int)$k, $v),
            array_keys($featureIdToValueMap),
            $featureIdToValueMap,
        ));

        dump($messages); // TODO: прокидывать результат на фронт
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
