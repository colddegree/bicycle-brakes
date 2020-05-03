<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Mapper\IntValueToArrayMapper;
use App\Mapper\RealValueToArrayMapper;
use App\Mapper\ScalarValueToArrayMapper;
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
            dump($request->request->all());
        }

        return $this->render('feature_possible_value/index.html.twig', [
            'data' => json_encode($this->getData(),  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
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
