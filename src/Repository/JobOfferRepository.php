<?php

namespace App\Repository;

use App\Entity\Job_offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\App_user;
use App\Service\GeonamesService;

class JobOfferRepository extends ServiceEntityRepository
{
    private $geonamesService;

    public function __construct(ManagerRegistry $registry, GeonamesService $geonamesService)
    {
        parent::__construct($registry, Job_offer::class);
        $this->geonamesService = $geonamesService;
    }

        public function findWithLocationFilter(
            string $searchTerm = '',
            array $filters = [],
            ?App_user $user = null,
            ?array $locationFilter = null

        ): array {
            $qb = $this->createQueryBuilder('j')
                ->leftJoin('j.user', 'u')   
                ->orderBy('j.date_posted', 'DESC');

            if ($searchTerm) {
                $qb->andWhere('LOWER(j.title) LIKE LOWER(:search) OR 
                            LOWER(j.description) LIKE LOWER(:search) OR
                            LOWER(j.skills) LIKE LOWER(:search) OR
                            LOWER(j.required_experience) LIKE LOWER(:search)')
                    ->setParameter('search', '%' . strtolower($searchTerm) . '%');
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

            if ($locationFilter) {
                $city = $locationFilter['city'];
                $radiusKm = $locationFilter['radius'] ?? 0;
                
                if ($radiusKm > 0) {
                    try {
                        // Get coordinates of the search city
                        $searchCoords = $this->geonamesService->fetchCityCoordinates($city);
                        
                        if ($searchCoords) {
                            // Get all job offers first (we'll filter them in PHP)
                            $allJobs = $qb->getQuery()->getResult();
                            
                            // Filter jobs by calculating distance to each one
                            $filteredJobs = [];
                            foreach ($allJobs as $job) {
                                // Extract city from address (assuming format "Country, City")
                                $addressParts = explode(', ', $job->getAddress());
                                $jobCity = end($addressParts);
                                
                                // Get coordinates for this job's city
                                $jobCoords = $this->geonamesService->fetchCityCoordinates($jobCity);
                                
                                if ($jobCoords) {
                                    // Calculate distance between search city and job city
                                    $distance = $this->calculateDistance(
                                        $searchCoords['lat'], 
                                        $searchCoords['lng'],
                                        $jobCoords['lat'],
                                        $jobCoords['lng']
                                    );
                                    
                                    if ($distance <= $radiusKm) {
                                        $filteredJobs[] = $job;
                                    }
                                }
                            }
                            
                            return $filteredJobs;
                        }
                    } catch (\Exception $e) {
                        // Fallback to exact match if API fails
                        $qb->andWhere('j.address LIKE :city')
                           ->setParameter('city', '%, ' . $city);
                    }
                } else {
                    // Exact city search
                    $qb->andWhere('j.address LIKE :city')
                       ->setParameter('city', '%, ' . $city);
                }
            }
            

            return $qb->getQuery()->getResult();
        }
        private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
        {
            $earthRadius = 6371; // Earth's radius in km
        
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
        
            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLon/2) * sin($dLon/2);
            
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            
            return $earthRadius * $c;
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

    public function findAllWithApplications(): array
    {
        return $this->createQueryBuilder('j')
            ->leftJoin('j.applications', 'a')
            ->leftJoin('a.user', 'u')
            ->leftJoin('a.coverletter', 'c')
            ->addSelect('a', 'u', 'c')
            ->orderBy('j.date_posted', 'DESC')
            ->getQuery()
            ->getResult();
    }


}
