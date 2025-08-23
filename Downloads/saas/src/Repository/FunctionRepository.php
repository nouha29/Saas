<?php
namespace App\Repository;

use App\Entity\Functions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FunctionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Functions::class);
    }
    public function findOneByIntitule(int $intitule): ?Functions
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.intitule = :intitule')
            ->setParameter('intitule', $intitule)
            ->getQuery()
            ->getOneOrNullResult();
    }
}