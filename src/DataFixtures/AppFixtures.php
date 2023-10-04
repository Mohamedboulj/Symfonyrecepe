<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $ingredients = [];
        for ($i = 0; $i < 30; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->word())
                ->setPrice($this->faker->numberBetween(2, 20));
            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }

        $recipes = [];
        for ($j = 0; $j < 20; $j++) {
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                ->setDuration(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                ->setnbPerson(mt_rand(0, 1) == 1 ? mt_rand(1, 50) : null)
                ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1, 5) : 0)
                ->setDescription($this->faker->text(255))
                ->setPrice(mt_rand(0, 1) == 1 ? mt_rand(1, 1000) : null)
                ->setIsFavorite(mt_rand(0, 1) == 1 ? true : 0)
                ->setIsPublic(mt_rand(0, 1) == 1 ? true : 0);

            for ($k = 0; $k < mt_rand(5, 15); $k++) {
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }
            $recipes[] = $recipe;
            $manager->persist($recipe);
        }
        for ($k = 0; $k < 10; $k++) {
            $user = new User();
            $user->setEmail($this->faker->email)
                ->setFullName($this->faker->firstName)
                ->setPseudo(mt_rand(0, 1) ? $this->faker->word : null)
                ->setRoles(['ROLE USER'])
                ->setPlainPassword('password');
            $manager->persist($user);
        }
        $manager->flush();
    }
}
