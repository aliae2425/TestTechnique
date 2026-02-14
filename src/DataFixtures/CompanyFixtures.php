<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\User; 
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CompanyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // On récupère le repository via l'ObjectManager pour avoir accès aux users déjà persistés
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll();

        foreach ($users as $user) {
            // On vérifie si l'utilisateur a le rôle ROLE_ENTREPRISE
            if (in_array('ROLE_ENTREPRISE', $user->getRoles())) {
                $company = new Company();
                $company->setName($faker->company());
                
                $manager->persist($company);
                
                // On associe la société à l'utilisateur
                $user->setCompany($company);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
