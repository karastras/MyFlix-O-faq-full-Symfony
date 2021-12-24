<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Character;
use App\Entity\Show;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {   
        $faker = Factory::create('fr_FR');

        // Création de séries de test
        for ($i = 0; $i < 10; $i++) {
            $show = new Show();
            $show->setTitle($faker->catchPhrase);

            // Création de catégories de test
            // On attribu à nos séries un nombre de catégories
            // compris (aléatoirement) entre 1 et 3
            for ($j = 0; $j < mt_rand(1,3); $j++) {
                $category = new Category();
                $category->setTitle($faker->catchPhrase);
                $show->addCategory($category);
                $manager->persist($category);
            }

            // Création de personnages de test
            // On attribu à nos séries un nombre de personnages
            // compris (aléatoirement) entre 1 et 3
            for ($j = 0; $j < mt_rand(1,5); $j++) {
                $character = new Character();
                $character->setFullname($faker->catchPhrase);
                $show->addCharacter($character);
                $manager->persist($character);
            }

            $manager->persist($show);
        }

        $manager->flush();
    }
}