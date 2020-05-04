<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeatureNormalValue;
use App\Entity\FeaturePossibleValue;
use App\IntervalMerger;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\IntValueToArrayMapper;
use App\Mapper\RealIntervalsToStringMapper;
use App\Mapper\RealValueToArrayMapper;
use App\Mapper\ScalarValueToArrayMapper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeatureNormalValueController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ObjectRepository $featureRepository;
    private ScalarValueToArrayMapper $scalarValueToArrayMapper;
    private IntValueToArrayMapper $intValueToArrayMapper;
    private RealValueToArrayMapper $realValueToArrayMapper;
    private IntervalMerger $intervalMerger;
    private IntIntervalsToStringMapper $intIntervalsToStringMapper;
    private RealIntervalsToStringMapper $realIntervalsToStringMapper;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScalarValueToArrayMapper $scalarValueToArrayMapper,
        IntValueToArrayMapper $intValueToArrayMapper,
        RealValueToArrayMapper $realValueToArrayMapper,
        IntervalMerger $intervalMerger,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper
    ) {
        $this->entityManager = $entityManager;
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->scalarValueToArrayMapper = $scalarValueToArrayMapper;
        $this->intValueToArrayMapper = $intValueToArrayMapper;
        $this->realValueToArrayMapper = $realValueToArrayMapper;
        $this->intervalMerger = $intervalMerger;
        $this->intIntervalsToStringMapper = $intIntervalsToStringMapper;
        $this->realIntervalsToStringMapper = $realIntervalsToStringMapper;
    }

    /**
     * @Route("/feature-normal-values")
     */
    public function index(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            dump($request->request->all());//TODO
        }

        return $this->render('feature_normal_value/index.html.twig', [
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
            $item['possibleValues'] = $this->mapPossibleValuesToArray($feature->type, $feature->possibleValues->toArray());
            $item['normalValues'] = $this->mapNormalValuesToArray($feature->type, $feature->normalValues->toArray());
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param int $type {@see Feature types}
     * @param FeaturePossibleValue[] $values
     *
     * @return array
     */
    private function mapPossibleValuesToArray(int $type, array $values): array
    {
        switch ($type) {
            case Feature::TYPE_SCALAR:
                return array_map(fn (FeaturePossibleValue $v) => $this->scalarValueToArrayMapper->map($v->scalarValue), $values);
            case Feature::TYPE_INT:
                return (array)$this->intIntervalsToStringMapper->map(
                    $this->intervalMerger->mergeInt(
                        array_map(static fn (FeaturePossibleValue $v) => $v->intValue, $values),
                    ),
                );
            case Feature::TYPE_REAL:
                return (array)$this->realIntervalsToStringMapper->map(
                    $this->intervalMerger->mergeReal(
                        array_map(static fn (FeaturePossibleValue $v) => $v->realValue, $values),
                    ),
                );
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $type));
        }
    }

    /**
     * @param int $type {@see Feature types}
     * @param FeatureNormalValue[] $values
     *
     * @return array
     */
    private function mapNormalValuesToArray(int $type, array $values): array
    {
        $result = [];

        foreach ($values as $value) {
            switch ($type) {
                case Feature::TYPE_SCALAR:
                    $result[] = $this->scalarValueToArrayMapper->map($value->scalarValue);
                    break;
                case Feature::TYPE_INT:
                    $result[] = $this->intValueToArrayMapper->map($value->intValue);
                    break;
                case Feature::TYPE_REAL:
                    $result[] = $this->realValueToArrayMapper->map($value->realValue);
                    break;
                default:
                    throw new RuntimeException(sprintf('Unsupported type "%s"', $type));
            }
        }

        return $result;
    }
}
