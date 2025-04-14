<?php

namespace App\Repository;

use App\Entity\Job_offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\App_user;

class JobOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job_offer::class);
    }

    public function findFilteredJobOffers(
        string $searchTerm = '',
        array $filters = [],
        ?App_user $user = null
    ): array {
        $qb = $this->createQueryBuilder('j')
            ->leftJoin('j.user', 'u')
            ->orderBy('j.date_posted', 'DESC');

        if ($searchTerm) {
            $qb->andWhere('LOWER(j.title) LIKE LOWER(:search) OR 
                          LOWER(j.description) LIKE LOWER(:search) OR
                          LOWER(j.skills) LIKE LOWER(:search) OR
                          LOWER(j.required_experience) LIKE LOWER(:search)')
               ->setParameter('search', '%'.strtolower($searchTerm).'%');
        }

        if (!empty($filters['types'])) {
            $qb->andWhere('j.type IN (:types)')
               ->setParameter('types', $filters['types']);
        }

        if (!empty($filters['fields'])) {
            $qb->andWhere('j.field IN (:fields)')
               ->setParameter('fields', $filters['fields']);
        }

        if (!empty($filters['education'])) {
            $qb->andWhere('j.required_education IN (:education)')
               ->setParameter('education', $filters['education']);
        }

        if ($user) {
            $qb->andWhere('u.id_user = :userId')
               ->setParameter('userId', $user->getId_user());
        }

        return $qb->getQuery()->getResult();
    }

    public function findRecentJobOffers(int $limit = 5): array
    {
        return $this->createQueryBuilder('j')
            ->orderBy('j.date_posted', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getUserStats(int $userId): array
    {
        $postCount = $this->createQueryBuilder('j')
            ->select('COUNT(j.id_offer)')
            ->where('j.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        $avgApplications = $this->createQueryBuilder('j')
            ->select('AVG(SIZE(j.applications))')
            ->where('j.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'active_posts' => $postCount,
            'avg_applications' => round($avgApplications, 1)
        ];
    }

    public function findMostPopularJobs(int $userId, int $limit = 5): array
    {
        return $this->createQueryBuilder('j')
            ->select('j.id_offer', 'j.title', 'COUNT(a.application_id) as application_count')
            ->leftJoin('j.applications', 'a')
            ->where('j.user = :user')
            ->setParameter('user', $userId)
            ->groupBy('j.id_offer', 'j.title')
            ->orderBy('application_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countJobsFromPreviousPeriod(): int
    {
        $previousPeriodStart = new \DateTime('-14 days');
        $previousPeriodEnd = new \DateTime('-7 days');
        
        return $this->createQueryBuilder('j')
            ->select('COUNT(j.id_offer)')
            ->where('j.date_posted BETWEEN :start AND :end')
            ->setParameter('start', $previousPeriodStart)
            ->setParameter('end', $previousPeriodEnd)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countJobsFromCurrentPeriod(): int
    {
        $currentPeriodStart = new \DateTime('-7 days');
        
        return $this->createQueryBuilder('j')
            ->select('COUNT(j.id_offer)')
            ->where('j.date_posted >= :start')
            ->setParameter('start', $currentPeriodStart)
            ->getQuery()
            ->getSingleScalarResult();
    }
}