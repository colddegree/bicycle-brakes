<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feature;
use App\Entity\IntValue;
use App\Entity\Malfunction;
use App\Entity\MalfunctionFeatureValueBind;
use App\Entity\RealValue;
use App\Entity\ScalarValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class MalfunctionFeatureValueBindFixture extends Fixture implements DependentFixtureInterface
{
    private const DATA = [
        [
            'malfunction' => 'malfunction_Загрязнение колодок маслом',
            'feature' => 'feature_Наличие скрипа при торможении',
            'values' => [
                'scalar_1_Да',
            ],
        ],
        [
            'malfunction' => 'malfunction_Загрязнение колодок маслом',
            'feature' => 'feature_Длина тормозного пути',
            'values' => [
                [
                    'lower' => 4.5,
                    'lowerIsInclusive' => false,
                    'upper' => 200,
                    'upperIsInclusive' => false,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Завоздушивание гидролинии',
            'feature' => 'feature_Расстояние от конца зажатой ручки тормоза до руля',
            'values' => [
                [
                    'lower' => 5,
                    'lowerIsInclusive' => true,
                    'upper' => 10,
                    'upperIsInclusive' => false,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Завоздушивание гидролинии',
            'feature' => 'feature_Характер упора при зажатии ручки тормоза',
            'values' => [
                'scalar_1_Плавный',
            ],
        ],
        [
            'malfunction' => 'malfunction_Нарушение герметичности гидролинии',
            'feature' => 'feature_Расстояние от конца зажатой ручки тормоза до руля',
            'values' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => true,
                    'upper' => 0,
                    'upperIsInclusive' => true,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Нарушение герметичности гидролинии',
            'feature' => 'feature_Длина тормозного пути',
            'values' => [
                [
                    'lower' => 200,
                    'lowerIsInclusive' => true,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Нарушение герметичности гидролинии',
            'feature' => 'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при зажатой ручке тормоза',
            'values' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Нарушение герметичности гидролинии',
            'feature' => 'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при зажатой ручке тормоза',
            'values' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Избыток тормозной жидкости',
            'feature' => 'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
            'values' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => true,
                    'upper' => 0.25,
                    'upperIsInclusive' => false,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Избыток тормозной жидкости',
            'feature' => 'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза',
            'values' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => true,
                    'upper' => 0.25,
                    'upperIsInclusive' => false,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Деформация ротора',
            'feature' => 'feature_Характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
            'values' => [
                'scalar_1_Прерывистый',
            ],
        ],
        [
            'malfunction' => 'malfunction_Деформация ротора',
            'feature' => 'feature_Равноудалённость колодок от ротора',
            'values' => [
                'scalar_3_Нет',
            ],
        ],
        [
            'malfunction' => 'malfunction_Износ фрикционов колодок',
            'feature' => 'feature_Толщина фрикциона внешней колодки',
            'values' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => true,
                    'upper' => 0.75,
                    'upperIsInclusive' => false,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Износ фрикционов колодок',
            'feature' => 'feature_Толщина фрикциона внутренней колодки',
            'values' => [
                [
                    'lower' => 0,
                    'lowerIsInclusive' => true,
                    'upper' => 0.75,
                    'upperIsInclusive' => false,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Износ ротора',
            'feature' => 'feature_Меньше ли толщина ротора допустимой для него толщины',
            'values' => [
                'scalar_2_Да',
            ],
        ],
        [
            'malfunction' => 'malfunction_Износ ротора',
            'feature' => 'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
            'values' => [
                [
                    'lower' => 1,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Износ ротора',
            'feature' => 'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза',
            'values' => [
                [
                    'lower' => 1,
                    'lowerIsInclusive' => false,
                    'upper' => RealValue::MAX,
                    'upperIsInclusive' => true,
                ],
            ],
        ],
        [
            'malfunction' => 'malfunction_Смещение калипера от плоскости ротора',
            'feature' => 'feature_Характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
            'values' => [
                'scalar_1_Постоянный',
            ],
        ],
        [
            'malfunction' => 'malfunction_Смещение калипера от плоскости ротора',
            'feature' => 'feature_Равноудалённость колодок от ротора',
            'values' => [
                'scalar_3_Нет',
            ],
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $entry) {
            /** @var Malfunction $malfunction */
            $malfunction = $this->getReference($entry['malfunction']);

            /** @var Feature $feature */
            $feature = $this->getReference($entry['feature']);

            $malfunctionFeatureValueBind = new MalfunctionFeatureValueBind($malfunction, $feature);

            foreach ($entry['values'] as $value) {
                switch ($feature->type) {
                    case Feature::TYPE_SCALAR:
                        /** @var ScalarValue $scalarValue */
                        $scalarValue = $this->getReference($value);
                        $malfunctionFeatureValueBind->scalarValues->add($scalarValue);
                        break;
                    case Feature::TYPE_INT:
                        $malfunctionFeatureValueBind->intValues->add(new IntValue($value['lower'], $value['upper']));
                        break;
                    case Feature::TYPE_REAL:
                        $malfunctionFeatureValueBind->realValues->add(new RealValue(
                            $value['lower'],
                            $value['lowerIsInclusive'],
                            $value['upper'],
                            $value['upperIsInclusive'],
                        ));
                        break;
                    default:
                        throw new RuntimeException(sprintf('Unsupported type "%s"', $feature->type));
                }
            }

            $manager->persist($malfunctionFeatureValueBind);
        }

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            MalfunctionFixture::class,
            FeatureFixture::class,
        ];
    }
}
