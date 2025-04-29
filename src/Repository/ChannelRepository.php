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
public function deleteChannelWithMessages(Channel $channel): void
{
    $em = $this->getEntityManager();
    
    // Delete all messages in this channel first
    $em->createQuery('
        DELETE FROM App\Entity\Message m 
        WHERE m.channel = :channel
    ')->setParameter('channel', $channel)
      ->execute();

    // Then delete the channel
    $em->remove($channel);
    $em->flush();
}
public function findChannelsWithLastMessage(int $userId): array
{
    // First get channels where user is either initiator or receiver
    $channels = $this->createQueryBuilder('c')
        ->where('c.initiator = :userId OR c.receiver = :userId')
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getResult();

    $results = [];
    $entityManager = $this->getEntityManager();

    foreach ($channels as $channel) {
        // Get last message using a separate query
        $lastMessage = $entityManager->createQuery('
            SELECT m 
            FROM App\Entity\Message m
            WHERE m.channel = :channel
            ORDER BY m.time_sent DESC
        ')
        ->setParameter('channel', $channel)
        ->setMaxResults(1)
        ->getOneOrNullResult();

        $results[] = [
            'channel' => $channel,
            'last_message' => $lastMessage
        ];
    }


    return $results;
}
}