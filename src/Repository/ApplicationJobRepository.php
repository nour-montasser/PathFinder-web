<?php

namespace App\Repository;

use App\Entity\ApplicationJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Job_offer;
use App\Entity\App_user;

class ApplicationJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationJob::class);
    }
    // src/Repository/ApplicationJobRepository.php
    public function hasUserAppliedToJob(int $userId, int $jobOfferId): bool
    {
        try {
            $count = $this->createQueryBuilder('a')
                ->select('COUNT(a.application_id)')
                ->where('a.user = :id_user')
                ->andWhere('a.jobOffer = :id_job_offer')
                ->setParameter('id_user', $userId)
                ->setParameter('id_job_offer', $jobOfferId)
                ->getQuery()
                ->getSingleScalarResult();
            
            // Debugging log to check the result
            // You can replace it with Symfony's logger service in production
            echo 'User ID: ' . $userId . ', Job Offer ID: ' . $jobOfferId . ', Count: ' . $count . PHP_EOL;
    
            return $count > 0;
        } catch (\Exception $e) {
            // Handle exception
            return false;
        }
    }
    
    // src/Repository/ApplicationJobRepository.php

public function findFilteredApplications(
    App_user $user,
    ?string $searchTerm = null,
    ?array $statuses = null, // Changed to nullable
    string $sort = 'date'
): array {
    $queryBuilder = $this->createQueryBuilder('a')
        ->leftJoin('a.jobOffer', 'j')
        ->leftJoin('j.user', 'u')
        ->where('a.user = :user')
        ->setParameter('user', $user);

    // Search filter
    if ($searchTerm) {
        $queryBuilder->andWhere('(j.title LIKE :search OR u.name LIKE :search)')
            ->setParameter('search', '%'.$searchTerm.'%');
    }

    // Status filter - only apply if statuses are provided
    if ($statuses !== null) {
        $queryBuilder->andWhere('a.status IN (:statuses)')
            ->setParameter('statuses', $statuses);
    }

    // Sorting
    if ($sort === 'alpha') {
        $queryBuilder->orderBy('j.title', 'ASC');
    } else {
        $queryBuilder->orderBy('a.date_application', 'DESC');
    }

    return $queryBuilder->getQuery()->getResult();
}


    /* public function countApplicationsForUserJobs(int $userId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)') // Count application IDs, not user IDs
            ->join('a.job_offer', 'j')
            ->join('j.user', 'u') // Join through job_offer to user
            ->where('u.id_user = :userId') // Match on user ID
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }*/


    // In ApplicationJobRepository.php
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



}
