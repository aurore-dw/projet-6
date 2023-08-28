<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Tricks;
use App\Entity\Comment;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        
        $faker = Faker\Factory::create('fr_FR');
        $categoriesDemoName = ['Grabs', 'Rotations', 'Flips', 'Rotations désaxées', 'Slides', 'One foot', 'Old school'];
        $tricksDemoName = ['Mute', 'Indy', '360', '720', 'Backflip', 'Misty', 'Tail slide', 'Method air', 'Backside air'];

        //Creation d'un utilisateur
        $user=new User();
        $user->setUsername($faker->username())
            ->setPassword(password_hash("1234", PASSWORD_DEFAULT))
            ->setEmail($faker->safeEmail())
            ->setRoles((array)'ROLE_USER')
            ->setProfilePicture('default.jpg');
        $manager->persist($user); 


        // 9 Tricks
        foreach ($tricksDemoName as $trickName)
        {
            $trick = new Tricks();
            $trick->setName($trickName)
                ->setDescription($faker->paragraph(2))
                ->setCreateAt(new \Datetime())
                ->setUser($user)
                ->setCategory($faker->randomElement($categoriesDemoName));

            // 3 images par trick
            for ($k=1; $k<4; $k++){
                $trick->addPicture('default.jpg');
            }

            // 1 à 4 vidéos par trick
            for ($l=0; $l<mt_rand(1, 4); $l++){
                $trick->setVideos('https://www.youtube.com/embed/SFYYzy0UF-8');
            }

            // 0 à 10 commentaires par trick
            for ($m=0; $m<mt_rand(0, 10); $m++){
                $comment = new Comment();
                $comment->setContent($faker->sentence(mt_rand(1, 3)))
                    ->setCreateAt(new \Datetime())
                    ->setUser($user)
                    ->setTrick($trick);

                $manager->persist($comment);
            }

            $manager->persist($trick);

        }

        $manager->flush();
    }
}
