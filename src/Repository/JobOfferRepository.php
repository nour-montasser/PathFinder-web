<?php

namespace App\Repository;

use App\Entity\JobOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\AppUser;

class JobOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }

   // src/Repository/JobOfferRepository.php
// src/Repository/JobOfferRepository.php

public function findFilteredJobOffers(
    string $searchTerm = '',
    array $filters = [],
    ?AppUser $user = null
): array {
    $qb = $this->createQueryBuilder('j')
        ->leftJoin('j.user', 'u')
        ->orderBy('j.datePosted', 'DESC');

    // Case-insensitive search across multiple fields
    if ($searchTerm) {
        $qb->andWhere('LOWER(j.title) LIKE LOWER(:search) OR 
                      LOWER(j.description) LIKE LOWER(:search) OR
                      LOWER(j.skills) LIKE LOWER(:search) OR
                      LOWER(j.requiredExperience) LIKE LOWER(:search)')
           ->setParameter('search', '%'.strtolower($searchTerm).'%');
    }

    // Apply filters if they exist in the $filters array
    if (!empty($filters['types'])) {
        $qb->andWhere('j.type IN (:types)')
           ->setParameter('types', $filters['types']);
    }

    if (!empty($filters['fields'])) {
        $qb->andWhere('j.field IN (:fields)')
           ->setParameter('fields', $filters['fields']);
    }

    if (!empty($filters['education'])) {
        $qb->andWhere('j.requiredEducation IN (:education)')
           ->setParameter('education', $filters['education']);
    }

    if ($user) {
        $qb->andWhere('u.id = :userId')
           ->setParameter('userId', $user->getId());
    }

    return $qb->getQuery()->getResult();
}       

public function findRecentJobOffers(int $limit = 5): array
{
    return $this->createQueryBuilder('j')
        ->orderBy('j.datePosted', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}
}