<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function insert(object $object): void
    {
        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush($object);
    }

    public function update(object $object): void
    {
        $this->getEntityManager()->flush($object);
    }

    public function delete(object $object): void
    {
        $this->getEntityManager()->remove($object);
    }

    public function deleteById(int $id): void
    {
        $object = $this->find($id);
        $this->getEntityManager()->remove($object);
    }
}