<?php

namespace App\Repository;

use App\Entity\Nomenclature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NomenclatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nomenclature::class);
    }

    public function save(Nomenclature $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) $this->_em->flush();
    }

    public function remove(Nomenclature $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) $this->_em->flush();
    }

    public function findAllWithProduit()
    {
        return $this->createQueryBuilder('n')
            ->addSelect('p')
            ->join('n.produit', 'p')
            ->getQuery()
            ->getResult();
    }
    public function findWithCompositions(int $id)
    {
        return $this->createQueryBuilder('n')
            ->leftJoin('n.compositions', 'c')
            ->addSelect('c')
            ->leftJoin('c.matiere', 'm')
            ->addSelect('m')
            ->where('n.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
