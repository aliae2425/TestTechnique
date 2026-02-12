<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @return Question[]
     */
    public function findRandomQuestions(int $limit): array
    {
        $ids = $this->createQueryBuilder('q')
             ->select('q.id')
             ->getQuery()
             ->getSingleColumnResult();
             
        if (empty($ids)) {
            return [];
        }

        shuffle($ids);
        $selectedIds = array_slice($ids, 0, $limit);
         
        return $this->createQueryBuilder('q')
             ->where('q.id IN (:ids)')
             ->setParameter('ids', $selectedIds)
             ->getQuery()
             ->getResult();
    }

    /**
     * @return Question[]
     */
    public function findByRule(string $theme, string $level, int $limit): array
    {
         $ids = $this->createQueryBuilder('q')
             ->select('q.id')
             ->where('q.type = :theme')
             ->andWhere('q.level = :level')
             ->setParameter('theme', $theme)
             ->setParameter('level', $level)
             ->getQuery()
             ->getSingleColumnResult();
         
        if (empty($ids)) {
            return [];
        }

        shuffle($ids);
        $selectedIds = array_slice($ids, 0, $limit);
         
        return $this->createQueryBuilder('q')
             ->where('q.id IN (:ids)')
             ->setParameter('ids', $selectedIds)
             ->getQuery()
             ->getResult();
    }

    public function getQuestionStats(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('q.id, q.titled, q.type, q.level, COUNT(ur.id) as totalAttempts, ' .
                     'SUM(CASE WHEN a.is_correct = true THEN 1 ELSE 0 END) as correctCount')
            ->from('App\Entity\Question', 'q')
            ->leftJoin('App\Entity\UserReponses', 'ur', 'WITH', 'ur.Question = q')
            ->leftJoin('ur.Reponse', 'a')
            ->groupBy('q.id')
            ->orderBy('totalAttempts', 'DESC')
            ->getQuery()
            ->getResult();
    }

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

    //    public function findOneBySomeField($value): ?Question
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
