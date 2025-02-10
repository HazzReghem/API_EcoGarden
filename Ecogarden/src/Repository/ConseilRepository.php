<?php

namespace App\Repository;

use App\Entity\Conseil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conseil>
 */
class ConseilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conseil::class);
    }

    // public function findByMonth(int $mois): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->where(':mois MEMBER OF c.months')
    //         ->setParameter('mois', $mois)
    //         ->getQuery()
    //         ->getResult();
    // }
}