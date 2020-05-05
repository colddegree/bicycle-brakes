<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feature;
use App\Entity\FeatureNormalValue;
use App\Entity\FeaturePossibleValue;
use App\Entity\IntValue;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class FeatureFixture extends Fixture implements DependentFixtureInterface
{
    private const DATA = [
        [
            'name' => 'Расстояние от конца зажатой ручки тормоза до руля',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 10,
                    'lowerIsInclusive' => true,
                    'upper' => 30,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Расстояние от конца зажатой ручки тормоза до руля',
        ],
        [
            'name' => 'Характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'scalar_1_Постоянный',
                'scalar_1_Прерывистый',
                'scalar_1_Отсутствие трения',
            ],
            'normalValues' => [
                'scalar_1_Отсутствие трения',
            ],
            '_ref' => 'feature_Характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
        ],
        [
            'name' => 'Наличие скрипа при торможении',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'scalar_1_Да',
                'scalar_1_Нет',
            ],
            'normalValues' => [
                'scalar_1_Нет',
            ],
            '_ref' => 'feature_Наличие скрипа при торможении',
        ],
        [
            'name' => 'Длина тормозного пути',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 2,
                    'lowerIsInclusive' => true,
                    'upper' => 4.5,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Длина тормозного пути',
        ],
        [
            'name' => 'Толщина фрикциона внешней колодки',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 0.75,
                    'lowerIsInclusive' => true,
                    'upper' => 5,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Толщина фрикциона внешней колодки',
        ],
        [
            'name' => 'Толщина фрикциона внутренней колодки',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 0.75,
                    'lowerIsInclusive' => true,
                    'upper' => 5,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Толщина фрикциона внутренней колодки',
        ],
        [
            'name' => 'Меньше ли толщина ротора допустимой для него толщины',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'scalar_2_Да',
                'scalar_2_Нет',
            ],
            'normalValues' => [
                'scalar_2_Нет',
            ],
            '_ref' => 'feature_Меньше ли толщина ротора допустимой для него толщины',
        ],
        [
            'name' => 'Расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 0.25,
                    'lowerIsInclusive' => true,
                    'upper' => 1,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
        ],
        [
            'name' => 'Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 0.25,
                    'lowerIsInclusive' => true,
                    'upper' => 1,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза',
        ],
        [
            'name' => 'Расстояние между фрикционом внешней колодки тормоза и ротором при зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => true,
                    'upper' => 0,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при зажатой ручке тормоза',
        ],
        [
            'name' => 'Расстояние между фрикционом внутренней колодки тормоза и ротором при зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => true,
                    'upper' => 0,
                    'upperIsInclusive' => true,
                ],
            ],
            '_ref' => 'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при зажатой ручке тормоза',
        ],
        [
            'name' => 'Равноудалённость колодок от ротора',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'scalar_3_Да',
                'scalar_3_Нет',
            ],
            'normalValues' => [
                'scalar_3_Да',
            ],
            '_ref' => 'feature_Равноудалённость колодок от ротора',
        ],
        [
            'name' => 'Характер упора при зажатии ручки тормоза',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'scalar_1_Чёткий, ярко выраженный',
                'scalar_1_Плавный',
            ],
            'normalValues' => [
                'scalar_1_Чёткий, ярко выраженный',
            ],
            '_ref' => 'feature_Характер упора при зажатии ручки тормоза',
        ],
        // TODO: remove
        [
            'name' => 'Тестовый целочисленный признак',
            'type' => Feature::TYPE_INT,
            'possibleValues' => [
                [
                    'lower' => -10,
                    'upper' => 20,
                ],
                [
                    'lower' => 0,
                    'upper' => 50,
                ],
            ],
            'normalValues' => [
                [
                    'lower' => 25,
                    'upper' => 30,
                ],
            ],
            '_ref' => 'feature_Тестовый целочисленный признак',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $entry) {
            $feature = new Feature($entry['name'], $entry['type']);

            foreach ($entry['possibleValues'] as $valueData) {
                $possibleValue = new FeaturePossibleValue($feature);
                $possibleValue = $this->populateValue($possibleValue, $valueData, $entry['type']);
                $manager->persist($possibleValue);
            }

            foreach ($entry['normalValues'] as $valueData) {
                $normalValue = new FeatureNormalValue($feature);
                $normalValue = $this->populateValue($normalValue, $valueData, $entry['type']);
                $manager->persist($normalValue);
            }

            $manager->persist($feature);
            $this->addReference($entry['_ref'], $feature);
        }

        $manager->flush();
    }

    /**
     * @param FeaturePossibleValue|FeatureNormalValue $value
     * @param array|string $valueData
     * @param int $type
     *
     * @return FeaturePossibleValue|FeatureNormalValue
     */
    private function populateValue($value, $valueData, int $type)
    {
        switch ($type) {
            case Feature::TYPE_SCALAR:
                /** @var ScalarValue $scalarValue */
                $scalarValue = $this->getReference($valueData);
                $value->scalarValue = $scalarValue;
                break;
            case Feature::TYPE_INT:
                $value->intValue = new IntValue($valueData['lower'], $valueData['upper']);
                break;
            case Feature::TYPE_REAL:
                $value->realValue = new RealValue(
                    $valueData['lower'],
                    $valueData['lowerIsInclusive'],
                    $valueData['upper'],
                    $valueData['upperIsInclusive'],
                );
                break;
            default:
                throw new RuntimeException(sprintf('Unsupported type "%s"', $type));
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            ScalarValueFixture::class,
        ];
    }
}
