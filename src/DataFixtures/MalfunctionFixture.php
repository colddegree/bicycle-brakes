<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feature;
use App\Entity\Malfunction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MalfunctionFixture extends Fixture implements DependentFixtureInterface
{
    private const DATA = [
        [
            'name' => 'Загрязнение колодок маслом',
            'clinicalPicture' => [
                'feature_Наличие скрипа при торможении',
                'feature_Длина тормозного пути',
            ],
            '_ref' => 'malfunction_Загрязнение колодок маслом',
        ],
        [
            'name' => 'Завоздушивание гидролинии',
            'clinicalPicture' => [
                'feature_Расстояние от конца зажатой ручки тормоза до руля',
                'feature_Характер упора при зажатии ручки тормоза',
            ],
            '_ref' => 'malfunction_Завоздушивание гидролинии',
        ],
        [
            'name' => 'Нарушение герметичности гидролинии',
            'clinicalPicture' => [
                'feature_Расстояние от конца зажатой ручки тормоза до руля',
                'feature_Длина тормозного пути',
                'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при зажатой ручке тормоза',
                'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при зажатой ручке тормоза',
            ],
            '_ref' => 'malfunction_Нарушение герметичности гидролинии',
        ],
        [
            'name' => 'Избыток тормозной жидкости',
            'clinicalPicture' => [
                'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
                'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза',
            ],
            '_ref' => 'malfunction_Избыток тормозной жидкости',
        ],
        [
            'name' => 'Деформация ротора',
            'clinicalPicture' => [
                'feature_Характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
                'feature_Равноудалённость колодок от ротора',
            ],
            '_ref' => 'malfunction_Деформация ротора',
        ],
        [
            'name' => 'Износ фрикционов колодок',
            'clinicalPicture' => [
                'feature_Толщина фрикциона внешней колодки',
                'feature_Толщина фрикциона внутренней колодки',
            ],
            '_ref' => 'malfunction_Износ фрикционов колодок',
        ],
        [
            'name' => 'Износ ротора',
            'clinicalPicture' => [
                'feature_Меньше ли толщина ротора допустимой для него толщины',
                'feature_Расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
                'feature_Расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза',
            ],
            '_ref' => 'malfunction_Износ ротора',
        ],
        [
            'name' => 'Смещение калипера от плоскости ротора',
            'clinicalPicture' => [
                'feature_Характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
                'feature_Равноудалённость колодок от ротора',
            ],
            '_ref' => 'malfunction_Смещение калипера от плоскости ротора',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $entry) {
            $malfunction = new Malfunction($entry['name']);

            foreach ($entry['clinicalPicture'] as $featureRef) {
                /** @var Feature $feature */
                $feature = $this->getReference($featureRef);
                $malfunction->features->add($feature);
            }

            $manager->persist($malfunction);
            $this->addReference($entry['_ref'], $malfunction);
        }

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            FeatureFixture::class,
        ];
    }
}
