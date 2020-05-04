<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Malfunction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MalfunctionFixture extends Fixture
{
    private const DATA = [
        [
            'name' => 'Загрязнение колодок маслом',
        ],
        [
            'name' => 'Завоздушивание гидролинии',
        ],
        [
            'name' => 'Нарушение герметичности гидролинии',
        ],
        [
            'name' => 'Избыток тормозной жидкости',
        ],
        [
            'name' => 'Деформация ротора',
        ],
        [
            'name' => 'Износ фрикционов колодок',
        ],
        [
            'name' => 'Износ ротора',
        ],
        [
            'name' => 'Смещение калипера от плоскости ротора',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $entry) {
            $malfunction = new Malfunction($entry['name']);

            $manager->persist($malfunction);
        }

        $manager->flush();
    }
}
