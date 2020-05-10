<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feature;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\RealValue;
use App\IntervalMerger;
use App\KnowledgeTree\SubsetValidator;
use App\Mapper\IntIntervalsToStringMapper;
use App\Mapper\RealIntervalsToStringMapper;
use App\Solver\FeatureDto;
use App\Solver\Solver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SolverController extends AbstractReactController
{
    private ObjectRepository $featureRepository;
    private Solver $solver;
    private SubsetValidator $subsetValidator;
    private IntIntervalsToStringMapper $intIntervalsToStringMapper;
    private RealIntervalsToStringMapper $realIntervalsToStringMapper;
    private IntervalMerger $intervalMerger;

    public function __construct(
        EntityManagerInterface $entityManager,
        Solver $solver,
        SubsetValidator $subsetValidator,
        IntIntervalsToStringMapper $intIntervalsToStringMapper,
        RealIntervalsToStringMapper $realIntervalsToStringMapper,
        IntervalMerger $intervalMerger
    ) {
        $this->featureRepository = $entityManager->getRepository(Feature::class);
        $this->solver = $solver;
        $this->subsetValidator = $subsetValidator;
        $this->intIntervalsToStringMapper = $intIntervalsToStringMapper;
        $this->realIntervalsToStringMapper = $realIntervalsToStringMapper;
        $this->intervalMerger = $intervalMerger;
    }

    /**
     * @Route("/решатель-задач", name="решатель-задач")
     */
    public function index(Request $request): Response
    {
        $pageName = 'Решатель задач';

        if ($request->isMethod(Request::METHOD_POST)) {
            $messages = $this->processRequest($request);
            $messages = array_map(static fn (string $s) => $s === '' ? '<br>' : $s, $messages);
            return $this->render('solver_result.html.twig', [
                'name' => $pageName . ' (решение)',
                'messages' => $messages,
            ]);
        }

        return $this->renderPageWithReact(
            $pageName,
            $pageName,
            $this->getData(),
            'solver',
            true,
        );
    }

    /**
     * @param Request $request
     *
     * @return string[] сообщения
     */
    private function processRequest(Request $request): array
    {
        $featureIdToValueMap = $request->request->all();

        $dtos = array_map(
            static fn($k, $v) => new FeatureDto((int)$k, $v),
            array_keys($featureIdToValueMap),
            $featureIdToValueMap,
        );

        $inputFeaturesArr = [];
        $isValid = true;
        foreach ($dtos as $dto) {
            [$arr, $isPossible] = $this->featureValueIsPossible($dto);
            $inputFeaturesArr[] = [
                'feature' => $arr,
                'isPossible' => $isPossible,
                'dto' => $dto,
            ];
            if (!$isPossible) {
                $isValid = false;
            }
        }

        $messages = ['Введены значения:'];

        foreach ($inputFeaturesArr as $arr) {
            /** @var Feature $f */
            $f = $arr['feature'];

            $isPossible = $arr['isPossible'];

            /** @var FeatureDto $dto */
            $dto = $arr['dto'];

            $messages[] = sprintf(
                '%s Признак "%s (#%d)", значение "%s" (%sвходит в подмножество возможных значений).',
                $isPossible ? '✅' : '❌',
                $f->name,
                $f->id,
                $this->mapValueToString($f, $dto),
                !$isPossible ? 'не ' : '',
            );
        }

        $messages[] = '';

        if (!$isValid) {
            $messages[] = sprintf(
                '❌ Одно из введённых значений не является возможным. <a href="%s">Попробуйте ввести снова.</a>',
                $this->generateUrl('решатель-задач'),
            );
            return $messages;
        }

        $messages[] = '✅ Задача решена!';
        $messages[] = '';
        $messages[] = 'Решение:';
        $messages = [
            ...$messages,
            ...$this->solver->solve(...$dtos),
        ];

        return $messages;
    }

    private function featureValueIsPossible(FeatureDto $dto): array
    {
        // копипаста из Solver

        /** @var Feature $f */
        $f = $this->featureRepository->find($dto->featureId);

        switch ($f->type) {
            case Feature::TYPE_SCALAR:
                $value = $f->possibleValues
                    ->filter(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->id === (int)$dto->value)
                    ->first()
                    ->scalarValue
                    ->value;
                $possibleValues = $f->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->value)
                    ->toArray();
                $isPossible = in_array($value, $possibleValues, true);
                break;
                
            case Feature::TYPE_INT:
                $value = (int)$dto->value;
                $possibleValues = $f->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->intValue)
                    ->toArray();
                $isPossible = $this->subsetValidator->checkAsAreSubsetOfBsInt([new IntValue($value, $value)], $possibleValues);
                break;
                
            case Feature::TYPE_REAL:
                $value = (float)$dto->value;
                $possibleValues = $f->possibleValues
                    ->map(static fn (FeaturePossibleValue $fpv) => $fpv->realValue)
                    ->toArray();
                $isPossible = $this->subsetValidator->checkAsAreSubsetOfBsReal(
                    [new RealValue($value, true, $value, true)],
                    $possibleValues,
                );
                break;

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $f->type));
        }
        
        return [$f, $isPossible];
    }

    private function mapValueToString(Feature $f, FeatureDto $dto): string
    {
        // копипаста из Solver

        switch ($f->type) {
            case Feature::TYPE_SCALAR:
                return $f->possibleValues
                    ->filter(static fn (FeaturePossibleValue $fpv) => $fpv->scalarValue->id === (int)$dto->value)
                    ->first()
                    ->scalarValue
                    ->value;

            case Feature::TYPE_INT:
                $value = (int)$dto->value;
                $str = $this->intIntervalsToStringMapper->map([new IntValue($value, $value)]);
                return substr($str, 1, -1); // вырезать фигурные скобки

            case Feature::TYPE_REAL:
                $value = (float)$dto->value;
                $str = $this->realIntervalsToStringMapper->map([new RealValue($value, true, $value, true)]);
                return substr($str, 1, -1); // вырезать фигурные скобки

            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $f->type));
        }
    }

    private function getData(): array
    {
        return [
            'features' => array_map(fn (Feature $f) => [
                'id' => $f->id,
                'name' => $f->name,
                'type' => $f->type,
                'possibleScalarValues' => $f->type !== Feature::TYPE_SCALAR
                    ? null
                    : $f->possibleValues->map(static fn (FeaturePossibleValue $fpv) => [
                        'id' => $fpv->scalarValue->id,
                        'value' => $fpv->scalarValue->value,
                    ])->toArray(),
                'possibleValueDomain' => $f->type === Feature::TYPE_SCALAR
                    ? null
                    : $this->mapFeatureNumericPossibleValuesToString($f),
            ], $this->featureRepository->findAll()),
        ];
    }

    private function mapFeatureNumericPossibleValuesToString(Feature $f): string
    {
        switch ($f->type) {
            case Feature::TYPE_INT:
                return $this->intIntervalsToStringMapper->map(
                    $this->intervalMerger->mergeInt(
                        $f->possibleValues->map(static fn (FeaturePossibleValue $fpv) => $fpv->intValue)->toArray(),
                    ),
                );
            case Feature::TYPE_REAL:
                return $this->realIntervalsToStringMapper->map(
                    $this->intervalMerger->mergeReal(
                        $f->possibleValues->map(static fn (FeaturePossibleValue $fpv) => $fpv->realValue)->toArray(),
                    ),
                );
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $f->type));
        }
    }
}
