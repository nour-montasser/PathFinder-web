<?php

// src/Repository/ChannelRepository.php
namespace App\Repository;

use App\Entity\Channel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.id_user1 = :userId OR c.id_user2 = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.time_created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUsers(int $user1Id, int $user2Id): ?Channel
    {
        return $this->createQueryBuilder('c')
            ->where('(c.id_user1 = :user1 AND c.id_user2 = :user2) OR (c.id_user1 = :user2 AND c.id_user2 = :user1)')
            ->setParameters([
                'user1' => $user1Id,
                'user2' => $user2Id
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}