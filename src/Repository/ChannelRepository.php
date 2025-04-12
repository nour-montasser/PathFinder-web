<?php

// src/Repository/ChannelRepository.php
namespace App\Repository;
use App\Entity\App_user;
use App\Entity\Channel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findUserChannels(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.initiator = :userId OR c.receiver = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.time_created', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findAvailableUsers(int $currentUserId): array
{
    return $this->getEntityManager()
        ->createQuery('
            SELECT u FROM App\Entity\App_user u
            WHERE u.id_user != :currentUserId
            ORDER BY u.name ASC
        ')
        ->setParameter('currentUserId', $currentUserId)
        ->getResult();
}
}