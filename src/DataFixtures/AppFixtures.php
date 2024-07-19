<?php

namespace App\DataFixtures;


use App\Entity\User;
use App\Entity\Customer;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create users
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            $manager->persist($user);

            // Create customers linked to the user
            for ($j = 0; $j < 3; $j++) {
                $customer = new Customer();
                $customer->setName($faker->lastName);
                $customer->setFirstName($faker->firstName);
                $customer->setEmail($faker->email);
                $customer->setCreatedAt($faker->dateTimeThisYear);
                $customer->setPhoneNumber($faker->phoneNumber);
                $customer->setAddress($faker->address);
                $customer->setUser($user);

                $manager->persist($customer);
            }
        }

        // Create products
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