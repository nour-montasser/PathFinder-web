<?php

namespace App\Repository;

use App\Entity\Skilltest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SkilltestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skilltest::class);
    }

    // Add custom methods as needed
}