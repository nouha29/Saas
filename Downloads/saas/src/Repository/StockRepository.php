<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function save(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByArticleAndDepot($articleId, $depotId)
    {
        return $this->createQueryBuilder('s')
            ->where('s.article = :articleId')
            ->andWhere('s.depot = :depotId')
            ->setParameter('articleId', $articleId)
            ->setParameter('depotId', $depotId)
            ->getQuery()
            ->getOneOrNullResult();
    }


public function findAllWithArticlesAndDepots()
{
    return $this->createQueryBuilder('s')
        ->leftJoin('s.article', 'a')
        ->addSelect('a')
        ->leftJoin('s.depot', 'd')
        ->addSelect('d')
        ->orderBy('s.dateEntree', 'DESC')
        ->getQuery()
        ->getResult();
}
}