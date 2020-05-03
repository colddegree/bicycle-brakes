<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feature;
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
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
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
        ],
        [
            'name' => 'Наличие скрипа при торможении',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'Да',
                'Нет',
            ],
        ],
        [
            'name' => 'Длина тормозного пути',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
                ],
            ],
        ],
        [
            'name' => 'Толщина фрикциона внешней колодки',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
                ],
            ],
        ],
        [
            'name' => 'Толщина фрикциона внутренней колодки',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
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
        ],
        [
            'name' => 'Расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
                ],
            ],
        ],
        [
            'name' => 'Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза,',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
                ],
            ],
        ],
        [
            'name' => 'Расстояние между фрикционом внешней колодки тормоза и ротором при зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
                ],
            ],
        ],
        [
            'name' => 'Расстояние между фрикционом внутренней колодки тормоза и ротором при зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL,
            'possibleValues' => [
                [
                    'lower' => 0,
                    'lower_is_inclusive' => false,
                    'upper' => RealValue::MAX,
                    'upper_is_inclusive' => true,
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
        ],
        [
            'name' => 'Характер упора при зажатии ручки тормоза',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'Чёткий, ярко выраженный',
                'Плавный',
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
        ],
    ];

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $entry) {
            $feature = new Feature($entry['name'], $entry['type']);

            foreach ($entry['possibleValues'] as $value) {
                $possibleValue = new FeaturePossibleValue($feature);

                switch ($entry['type']) {
                    case Feature::TYPE_SCALAR:
                        $possibleValue->scalarValue = new ScalarValue($value);
                        break;
                    case Feature::TYPE_INT:
                        $possibleValue->intValue = new IntValue($value['lower'], $value['upper']);
                        break;
                    case Feature::TYPE_REAL:
                        $possibleValue->realValue = new RealValue(
                            $value['lower'],
                            $value['lower_is_inclusive'],
                            $value['upper'],
                            $value['upper_is_inclusive'],
                        );
                        break;
                    default:
                        throw new RuntimeException(sprintf('Unsupported type "%s"', $entry['type']));
                }

                $manager->persist($possibleValue);
            }

            $manager->persist($feature);
        }

        $manager->flush();
    }
}
