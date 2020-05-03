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
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class FeatureFixture extends Fixture
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
        ],
        [
            'name' => 'Характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'Постоянный',
                'Прерывистый',
                'Отсутствие трения',
            ],
            'normalValues' => [
                'Отсутствие трения',
            ],
        ],
        [
            'name' => 'Наличие скрипа при торможении',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'Да',
                'Нет',
            ],
            'normalValues' => [
                'Нет',
            ],
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
        ],
        [
            'name' => 'Меньше ли толщина ротора допустимой для него толщины',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'Да',
                'Нет',
            ],
            'normalValues' => [
                'Нет',
            ],
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
        ],
        [
            'name' => 'Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза,',
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
        ],
        [
            'name' => 'Равноудалённость колодок от ротора',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'Да',
                'Нет',
            ],
            'normalValues' => [
                'Да',
            ],
        ],
        [
            'name' => 'Характер упора при зажатии ручки тормоза',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'Чёткий, ярко выраженный',
                'Плавный',
            ],
            'normalValues' => [
                'Чёткий, ярко выраженный',
            ],
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
                $this->populateValue($possibleValue, $valueData, $entry['type']);
                $manager->persist($possibleValue);
            }

            foreach ($entry['normalValues'] as $valueData) {
                $normalValue = new FeatureNormalValue($feature);
                $this->populateValue($normalValue, $valueData, $entry['type']);
                $manager->persist($normalValue);
            }

            $manager->persist($feature);
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
                $value->scalarValue = new ScalarValue($valueData);
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
}
