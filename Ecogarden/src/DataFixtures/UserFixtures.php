<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setEmail($faker->email);
            $user->setCity($faker->city);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword(
                $user,
                'password'
            ));
            $manager->persist($user);
        }

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@mail.com');
        $admin->setCity('Paris');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword(
            $admin,
            'password'
        ));
        $manager->persist($admin);

        $manager->flush();
    }
}
