<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            WishFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");

        for ($i = 0; $i < 300; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->realText(500));
            $comment->setNote($faker->numberBetween(0, 5));

            $comment->setCreatedAt(\DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-2 months', 'now')
            ));

            $userRef = 'user-' . $faker->numberBetween(0, 4);
            $comment->setUser($this->getReference($userRef, User::class));

            $wishRef = 'wish-' . $faker->numberBetween(0, 99);
            $comment->setWish($this->getReference($wishRef, Wish::class));


            $manager->persist($comment);
        }

        $manager->flush();
    }
}
