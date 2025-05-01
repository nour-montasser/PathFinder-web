<?php

namespace App\Repository;

use App\Entity\ApplicationJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Job_offer;
use App\Entity\App_user;
use Doctrine\DBAL\Query;
use Doctrine\Migrations\Query\Query as QueryQuery;
use Doctrine\ORM\Query as ORMQuery;

class ApplicationJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationJob::class);
    }

    public function hasUserAppliedToJob(int $userId, int $jobOfferId): bool
    {
        $count = $this->createQueryBuilder('a')
            ->select('COUNT(a.application_id)')
            ->where('a.user = :id_user')
            ->andWhere('a.jobOffer = :id_job_offer')
            ->setParameter('id_user', $userId)
            ->setParameter('id_job_offer', $jobOfferId)
            ->getQuery()
            ->getSingleScalarResult();
        
        return $count > 0;
    }

    public function countUserApplicationsForCompany(int $userId, int $companyId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.application_id)')
            ->join('a.jobOffer', 'j') // Corrected property name
            ->join('j.user', 'c')      // Join with company (user with role=1)
            ->where('a.user = :userId')
            ->andWhere('c.id_user = :companyId')
            ->setParameter('userId', $userId)
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    

    public function findFilteredApplicationsQuery(
        App_user $user,
        ?string $searchTerm = null,
        ?array $statuses = null,
        string $sort = 'date'
    ):ORMQuery{
        $queryBuilder = $this->createQueryBuilder('a')
            ->leftJoin('a.jobOffer', 'j')
            ->leftJoin('j.user', 'u')
            ->where('a.user = :user')
            ->setParameter('user', $user);

        if ($searchTerm) {
            $queryBuilder->andWhere('(j.title LIKE :search OR u.name LIKE :search)')
                ->setParameter('search', '%'.$searchTerm.'%');
        }

        if ($statuses !== null) {
            $queryBuilder->andWhere('a.status IN (:statuses)')
                ->setParameter('statuses', $statuses);
        }

        if ($sort === 'alpha') {
            $queryBuilder->orderBy('j.title', 'ASC');
        } else {
            $queryBuilder->orderBy('a.date_application', 'DESC');
        }

        return $queryBuilder->getQuery();
    }

    public function findUserApplicationForJob(int $userId, int $jobOfferId): ?ApplicationJob
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :userId')
            ->andWhere('a.jobOffer = :jobOfferId')
            ->setParameter('userId', $userId)
            ->setParameter('jobOfferId', $jobOfferId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countApplicationsForCompany(int $userId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.application_id)')
            ->join('a.jobOffer', 'j')
            ->where('j.user = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getApplicationStatusStats(int $userId): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.application_id) as count')
            ->join('a.jobOffer', 'j')
            ->where('j.user = :user')
            ->setParameter('user', $userId)
            ->groupBy('a.status')
            ->getQuery()->getResult();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['status']] = $result['count'];
        }

        return $stats;
    }

    public function findRecentApplicationsForCompany(int $userId, int $limit = 5): array
{
    return $this->createQueryBuilder('a')
        ->select([
            'a.application_id',
            'a.status', 
            'a.date_application as applicationDate', // Aliased here
            'j.title as jobTitle',  
            'u.name as candidateName'
        ])
        ->join('a.jobOffer', 'j')
        ->join('a.user', 'u')
        ->where('j.user = :user')
        ->setParameter('user', $userId)
        ->orderBy('a.date_application', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

    public function calculateConversionRate(): float
{
    // Get total job offers
    $totalJobOffers = $this->getEntityManager()
        ->createQuery('SELECT COUNT(j.id_offer) FROM App\Entity\Job_offer j')
        ->getSingleScalarResult();

    // Get total applications
    $totalApplications = $this->createQueryBuilder('a')
        ->select('COUNT(a.application_id)')
        ->getQuery()
        ->getSingleScalarResult();

    return $totalJobOffers > 0 ? round(($totalApplications / $totalJobOffers) * 100, 2) : 0;
}

public function calculateConversionRateChange(): float
{
    // Current period (last 7 days)
    $currentStart = new \DateTime('-7 days');
    
    $currentApplications = $this->createQueryBuilder('a')
        ->select('COUNT(a.application_id)')
        ->where('a.date_application >= :currentStart')
        ->setParameter('currentStart', $currentStart)
        ->getQuery()
        ->getSingleScalarResult();

    $currentJobs = $this->getEntityManager()
        ->createQuery('SELECT COUNT(j.id_offer) FROM App\Entity\Job_offer j WHERE j.date_posted >= :currentStart')
        ->setParameter('currentStart', $currentStart)
    
        ->getSingleScalarResult();

    $currentRate = $currentJobs > 0 ? ($currentApplications / $currentJobs) * 100 : 0;

    // Previous period (7-14 days ago)
    $previousStart = new \DateTime('-14 days');
    $previousEnd = new \DateTime('-7 days');

    $previousApplications = $this->createQueryBuilder('a')
        ->select('COUNT(a.application_id)')
        ->where('a.date_application BETWEEN :previousStart AND :previousEnd')
        ->setParameter('previousStart', $previousStart)
        ->setParameter('previousEnd', $previousEnd)
        ->getQuery()
        ->getSingleScalarResult();

    $previousJobs = $this->getEntityManager()
        ->createQuery('SELECT COUNT(j.id_offer) FROM App\Entity\Job_offer j WHERE j.date_posted BETWEEN :previousStart AND :previousEnd')
        ->setParameter('previousStart', $previousStart)
        ->setParameter('previousEnd', $previousEnd)
       
        ->getSingleScalarResult();

    $previousRate = $previousJobs > 0 ? ($previousApplications / $previousJobs) * 100 : 0;

    return $previousRate > 0 ? round((($currentRate - $previousRate) / $previousRate * 100), 2) : 0;
}

public function getWeeklyApplicationTrends(int $userId, int $weeks = 8): array
{
    return $this->createQueryBuilder('a')
        ->select([
            "IDENTITY(a.jobOffer) as job_id",
            "a.status",
            "COUNT(a.application_id) as count"
        ])
        ->join('a.jobOffer', 'j')
        ->where('j.user = :user')
        ->andWhere('a.date_application >= :startDate')
        ->setParameter('user', $userId)
        ->setParameter('startDate', new \DateTime("-$weeks weeks"))
        ->groupBy('job_id, a.status')
        ->getQuery()
        ->getResult();
}

public function getApplicationTrends(int $userId, int $weeks = 4): array
{
    $endDate = new \DateTime();
    $startDate = clone $endDate;
    $startDate->modify("-$weeks weeks");
    
    $results = $this->createQueryBuilder('a')
        ->select([
            "a.date_application as date",
            "a.status",
            "COUNT(a.application_id) as count"
        ])
        ->join('a.jobOffer', 'j')
        ->where('j.user = :user')
        ->andWhere('a.date_application BETWEEN :startDate AND :endDate')
        ->setParameter('user', $userId)
        ->setParameter('startDate', $startDate)
        ->setParameter('endDate', $endDate)
        ->groupBy('a.date_application, a.status')
        ->orderBy('a.date_application')
        ->getQuery()
        ->getResult();

    // Process dates in PHP
    $processed = [];
    foreach ($results as $item) {
        $date = $item['date'] instanceof \DateTimeInterface ? $item['date'] : new \DateTime($item['date']);
        $processed[] = [
            'date' => $date->format('Y-m-d'),
            'day_name' => $date->format('l'), // Full day name (Monday, Tuesday, etc.)
            'status' => $item['status'],
            'count' => (int)$item['count']
        ];
    }
    
    return $processed;
}

}