<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\User;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');


        for ($i = 0; $i < 150; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email());
            $user->setPassword('$2y$13$.m2Fq.wgw83oZGeaDTCw/e.z3gSjoCa5vHQaC6pueAuw8g96aXQi'); // password
            
            // Randomly assign roles for chart data
            if ($faker->boolean(20)) { // 20% enterprises
                $user->setRoles(['ROLE_ENTREPRISE']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $user->setName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setIsVerified($faker->boolean(80));
            
            // Generate random date within the last year for the chart
            $date = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now'));
            $user->setCreatedAt($date);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
