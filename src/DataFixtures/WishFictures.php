<?php

namespace App\DataFixtures;

use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class WishFictures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        for ($i = 0; $i < 20; $i++) {
            $wish = new Wish();
            $wish->setTitle($faker->realText(50));
            $wish->setDescription($faker->realText(200));
            $wish->setAuthor($faker->name());
            $wish->setIsPublished($faker->boolean(50));
            $wish->setIsCompleted($faker->boolean(30));
            $wish->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeThisDecade()));

            $manager->persist($wish);
        }

        $manager->flush();
    }
}
