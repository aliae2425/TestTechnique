<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\QuizTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizTemplate>
 */
class QuizTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizTemplate::class);
    }

    //    /**
    //     * @return QuizTemplate[] Returns an array of QuizTemplate objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /** @return QuizTemplate[] */
    public function findByCompany(Company $company): array
    {
        return $this->findBy(['company' => $company], ['Titre' => 'ASC']);
    }

    /** @return QuizTemplate[] quiz plateforme (company IS NULL) */
    public function findPlatform(): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.company IS NULL')
            ->orderBy('q.Titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
