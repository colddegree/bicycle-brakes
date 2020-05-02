<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ScalarValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ScalarValueFixture extends Fixture
{
    private const DATA = [
        [
            'name' => 'Да',
            '_ref' => 'scalar_Да',
        ],
        [
            'name' => 'Нет',
            '_ref' => 'scalar_Нет',
        ],
        [
            'name' => 'Постоянный',
            '_ref' => 'scalar_Постоянный',
        ],
        [
            'name' => 'Прерывистый',
            '_ref' => 'scalar_Прерывистый',
        ],
        [
            'name' => 'Отсутствие трения',
            '_ref' => 'scalar_Отсутствие трения',
        ],
        [
            'name' => 'Чёткий, ярко выраженный',
            '_ref' => 'scalar_Чёткий, ярко выраженный',
        ],
        [
            'name' => 'Плавный',
            '_ref' => 'scalar_Плавный',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::DATA as $entry) {
            $scalarValue = new ScalarValue($entry['name']);
            $this->addReference($entry['_ref'], $scalarValue);

            $manager->persist($scalarValue);
        }

        $manager->flush();
    }
}