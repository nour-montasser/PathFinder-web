<?php

namespace App\Repository;

use App\Entity\App_user;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class App_userRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, App_user::class);
    }

    public function nourfindCredentials(string $email, string $plainPassword): ?App_user
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->andWhere('u.password = :password')
            ->setParameter('email', $email)
            ->setParameter('password', $plainPassword)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    
    
    
}