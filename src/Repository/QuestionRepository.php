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

    public function getQuestionStats(string $sortField = 'totalAttempts', string $sortOrder = 'DESC'): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('q.id, q.titled, q.type, q.level, COUNT(ur.id) as totalAttempts, ' .
                     'SUM(CASE WHEN a.is_correct = true THEN 1 ELSE 0 END) as correctCount')
            ->from('App\Entity\Question', 'q')
            ->leftJoin('App\Entity\UserReponses', 'ur', 'WITH', 'ur.Question = q')
            ->leftJoin('ur.Reponse', 'a')
            ->groupBy('q.id');

        // Map frontend sort fields to DQL fields
        switch ($sortField) {
            case 'id':
                $qb->orderBy('q.id', $sortOrder);
                break;
            case 'totalAttempts':
                $qb->orderBy('totalAttempts', $sortOrder);
                break;
            case 'successRate':
                // Complex sorting for calculated field. 
                // We add a custom HIDDEN field to sort by it
                $qb->addSelect('(SUM(CASE WHEN a.is_correct = true THEN 1 ELSE 0 END) * 1.0 / NULLIF(COUNT(ur.id), 0)) as HIDDEN successRateVal');
                $qb->orderBy('successRateVal', $sortOrder);
                break;
            default:
                $qb->orderBy('totalAttempts', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function getAnswerDistribution(int $questionId): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('a.text, a.is_correct, COUNT(ur.id) as count')
            ->from('App\Entity\Answer', 'a')
            ->leftJoin('App\Entity\UserReponses', 'ur', 'WITH', 'ur.Reponse = a')
            ->where('a.question = :questionId')
            ->setParameter('questionId', $questionId)
            ->groupBy('a.id')
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
