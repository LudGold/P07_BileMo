<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName($faker->word);
            $product->setDescription($faker->sentence);
            $product->setBrand($faker->company);
            $product->setModel($faker->randomFloat(2, 1, 100));
            $product->setPrice($faker->numberBetween(10, 1000));
            $product->setStock($faker->numberBetween(0, 100));
            $product->setDateAdded($faker->dateTimeThisYear);
            $product->setTechnicalSpecs($faker->words(5));
            $product->setImages([$faker->imageUrl]);
            $product->setCategory($faker->word);
            $product->setAvailableColors($faker->words(3));
            $product->setState($faker->word);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
