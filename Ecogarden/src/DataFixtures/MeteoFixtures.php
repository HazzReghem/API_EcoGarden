<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Meteo;
use Faker\Factory;
use DateTime;

class MeteoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for($i = 0; $i < 5; $i++) {
            $meteo = new Meteo();
            $meteo->setCity($faker->city);
            $meteo->setData(([
                'temperature' => $faker->randomFloat(1, -10, 35),
                'humidity' => $faker->numberbetween(30, 100),
                'condition' => $faker->randomElement(['Ensoleillé', 'Pluvieux', 'Nuageux', 'Orageux', 'Neigeux'])
            ]));
            $meteo->setLastFetchedAt(new \DateTime());

            $manager->persist($meteo);
        }

        $manager->flush();
    }
}
