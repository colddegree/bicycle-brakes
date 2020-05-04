<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ScalarValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ScalarValueFixture extends Fixture
{
    /**
     * Важно! Можно переиспользовать только в рамках одного признака!
     */
    private const DATA = [
        [
            'name' => 'Постоянный',
            '_ref' => 'scalar_1_Постоянный',
        ],
        [
            'name' => 'Прерывистый',
            '_ref' => 'scalar_1_Прерывистый',
        ],
        [
            'name' => 'Отсутствие трения',
            '_ref' => 'scalar_Отсутствие трения',
        ],
        [
            'name' => 'Да',
            '_ref' => 'scalar_1_Да',
        ],
        [
            'name' => 'Нет',
            '_ref' => 'scalar_1_Нет',
        ],
        [
            'name' => 'Да',
            '_ref' => 'scalar_2_Да',
        ],
        [
            'name' => 'Нет',
            '_ref' => 'scalar_2_Нет',
        ],
        [
            'name' => 'Да',
            '_ref' => 'scalar_3_Да',
        ],
        [
            'name' => 'Нет',
            '_ref' => 'scalar_3_Нет',
        ],
        [
            'name' => 'Чёткий, ярко выраженный',
            '_ref' => 'scalar_Чёткий, ярко выраженный',
        ],
        [
            'name' => 'Плавный',
            '_ref' => 'scalar_1_Плавный',
        ],
        [
            'name' => 'Да',
            '_ref' => 'scalar_4_Да',
        ],
        [
            'name' => 'Плавный',
            '_ref' => 'scalar_2_Плавный',
        ],
        [
            'name' => 'Прерывистый',
            '_ref' => 'scalar_2_Прерывистый',
        ],
        [
            'name' => 'Нет',
            '_ref' => 'scalar_4_Нет',
        ],
        [
            'name' => 'Да',
            '_ref' => 'scalar_5_Да',
        ],
        [
            'name' => 'Постоянный',
            '_ref' => 'scalar_2_Постоянный',
        ],
        [
            'name' => 'Нет',
            '_ref' => 'scalar_5_Нет',
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
