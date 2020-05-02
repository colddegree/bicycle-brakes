<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feature;
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
                'scalar_Постоянный',
                'scalar_Прерывистый',
                'scalar_Отсутствие трения',
            ],
        ],
        [
            'name' => 'Наличие скрипа при торможении',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'scalar_Да',
                'scalar_Нет',
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
                'scalar_Да',
                'scalar_Нет',
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
                'scalar_Да',
                'scalar_Нет',
            ],
        ],
        [
            'name' => 'Характер упора при зажатии ручки тормоза',
            'type' => Feature::TYPE_SCALAR,
            'possibleValues' => [
                'scalar_Чёткий, ярко выраженный',
                'scalar_Плавный',
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
                        /** @var ScalarValue $value */
                        $value = $this->getReference($value);
                        $possibleValue->scalarValue = $value;
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
