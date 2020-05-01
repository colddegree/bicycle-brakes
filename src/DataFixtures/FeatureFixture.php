<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feature;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FeatureFixture extends Fixture
{
    private const DATA = [
        [
            'name' => 'расстояние от конца зажатой ручки тормоза до руля',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'характер трения колодок о ротор при вращении колеса с тормозной ручкой в состоянии покоя',
            'type' => Feature::TYPE_SCALAR,
        ],
        [
            'name' => 'наличие скрипа при торможении',
            'type' => Feature::TYPE_SCALAR,
        ],
        [
            'name' => 'длина тормозного пути',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'толщина фрикциона внешней колодки',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'толщина фрикциона внутренней колодки',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'меньше ли толщина ротора допустимой для него толщины',
            'type' => Feature::TYPE_SCALAR,
        ],
        [
            'name' => 'расстояние между фрикционом внешней колодки тормоза и ротором при не зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'расстояние между фрикционом внутренней колодки тормоза и ротором при не зажатой ручке тормоза,',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'расстояние между фрикционом внешней колодки тормоза и ротором при зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'расстояние между фрикционом внутренней колодки тормоза и ротором при зажатой ручке тормоза',
            'type' => Feature::TYPE_REAL
        ],
        [
            'name' => 'равноудалённость колодок от ротора',
            'type' => Feature::TYPE_SCALAR,
        ],
        [
            'name' => 'характер упора при зажатии ручки тормоза',
            'type' => Feature::TYPE_SCALAR,
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $entry) {
            $feature = new Feature($entry['name'], $entry['type']);
            $manager->persist($feature);
        }

        $manager->flush();
    }
}
