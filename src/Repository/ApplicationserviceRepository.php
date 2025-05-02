<?php

namespace App\Repository;

use App\Entity\Applicationservice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApplicationserviceRepository extends ServiceEntityRepository
{
    
    public function __construct(ManagerRegistry $registry)
    {
        
   
        parent::__construct($registry, Applicationservice::class);
    }

    // Add custom methods as needed
}