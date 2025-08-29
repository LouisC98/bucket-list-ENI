<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class WishFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        for ($i = 0; $i < 100; $i++) {
            $wish = new Wish();
            $wish->setTitle($faker->realText(50));
            $wish->setDescription($faker->realText(500));
            $wish->setIsPublished($faker->boolean(70));
            $wish->setIsCompleted($faker->boolean(20));

            $wish->setCreatedAt(\DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-2 years', 'now')
            ));

            $userRef = 'user-' . $faker->numberBetween(0, 4);
            $wish->setAuthor($this->getReference($userRef, User::class));

            $manager->persist($wish);
        }

        $manager->flush();
    }
}
