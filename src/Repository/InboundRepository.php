<?php

namespace App\Repository;

use App\Entity\Inbound;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Inbound|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inbound|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inbound[]    findAll()
 * @method Inbound[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InboundRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inbound::class);
    }
}
