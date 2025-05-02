<?php

namespace App\Repository;

use App\Entity\Skilltest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skilltest>
 */
class SkilltestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skilltest::class);
    }

    public function findSkilltestWithQuestions($id): ?Skilltest
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.questions', 'q')
            ->addSelect('q')
            ->where('s.id_test = :id') // only valid if `id_test` is a property
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
