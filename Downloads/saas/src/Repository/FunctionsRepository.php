<?php

namespace App\Repository;

use App\Entity\Functions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Functions>
 *
 * @method Functions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Functions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Functions[]    findAll()
 * @method Functions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FunctionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Functions::class);
    }

    public function save(Functions $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Functions $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}