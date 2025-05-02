<?php

namespace App\Repository;

use App\Entity\Serviceoffre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceoffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Serviceoffre::class);
    }

    public function filterServices(?string $field, ?string $skill, ?string $search, array $durations, array $prices, ?string $sort): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($field) {
            $qb->andWhere('s.field = :field')
               ->setParameter('field', $field);
        }

        if ($skill) {
            $qb->andWhere('s.skills LIKE :skill')
               ->setParameter('skill', '%' . $skill . '%');
        }

        if ($search) {
            $qb->andWhere('s.title LIKE :search OR s.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($durations)) {
            $qb->andWhere('s.duration IN (:durations)')
               ->setParameter('durations', $durations);
        }

        if (!empty($prices)) {
            $or = $qb->expr()->orX();
            foreach ($prices as $range) {
                switch ($range) {
                    case 'Less than $100':
                        $or->add($qb->expr()->lt('s.price', 100));
                        break;
                    case '$100 to $500':
                        $or->add($qb->expr()->between('s.price', 100, 500));
                        break;
                    case '$500 to $1K':
                        $or->add($qb->expr()->between('s.price', 500, 1000));
                        break;
                    case '$1K+':
                        $or->add($qb->expr()->gt('s.price', 1000));
                        break;
                }
            }
            $qb->andWhere($or);
        }

        // Updated to use date_posted instead of datePosted
        if ($sort === 'newest') {
            $qb->orderBy('s.date_posted', 'DESC');
        } elseif ($sort === 'oldest') {
            $qb->orderBy('s.date_posted', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }


    public function findFreelancersHiredByClient($client)
{
    return $this->createQueryBuilder('a')
        ->join('a.service', 's')
        ->where('s.client = :client')
        ->andWhere('a.status = :status')
        ->setParameter('client', $client)
        ->setParameter('status', 'paid')
        ->getQuery()
        ->getResult();
}

}