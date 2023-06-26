<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Tricks;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $faker = Faker\Factory::create('fr_FR');

        //Creation d'un utilisateur
        $user=new User();
        $tricks= new Tricks();

        $user->setUsername($faker->username())
            ->setPassword(password_hash("1234", PASSWORD_DEFAULT));

        $tricks->setName($faker->name())
                ->setDescription("test")
                ->setCreateAt("2023-03-06 08:42:04")
                ->setUser($faker->username())
                ->setPictures("test")
                ->setVideos("test")
                ->setCategory("test");

        $manager->persist($user, $tricks);

        $manager->flush();
    }
}
