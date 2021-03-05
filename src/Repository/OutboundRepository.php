<?php

namespace App\Repository;

use App\Entity\Outbound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Outbound|null find($id, $lockMode = null, $lockVersion = null)
 * @method Outbound|null findOneBy(array $criteria, array $orderBy = null)
 * @method Outbound[]    findAll()
 * @method Outbound[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OutboundRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outbound::class);
    }
}
