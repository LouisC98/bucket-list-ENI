<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class WishFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
        ];
    }
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

            $categoryRef = 'category-' . $faker->numberBetween(0, 9);
            $wish->setCategory($this->getReference($categoryRef, Category::class));

            $manager->persist($wish);

            $this->addReference('wish-' . $i, $wish);
        }

        $manager->flush();
    }
}
