<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTime;
use App\Entity\Conseil;
use Faker\Factory;

class ConseilFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for($i = 0; $i < 5; $i++) {
            $conseil = new Conseil();
            $conseil->setContent($faker->paragraph(3));
            $conseil->setMonths($faker->randomElements(range(1, 12), rand(1, 6)));

            $conseil->setCreatedAt($faker->dateTimeBetween('-6 months', 'now'));
            $conseil->setUpdatedAt($faker->dateTimeBetween($conseil->getCreatedAt()));

            $manager->persist($conseil);
        }

        $manager->flush();
    }
}
