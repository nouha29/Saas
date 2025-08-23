<?php
namespace App\Repository;

use App\Entity\Articles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Articles>
 */
class ArticlesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Articles::class);
    }

    public function save(Articles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Articles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generateReference(string $prefix): string
    {
        $year = date('y'); 
                $lastArticle = $this->createQueryBuilder('a')
            ->where('a.reference LIKE :prefix')
            ->setParameter('prefix', $prefix.$year.'%')
            ->orderBy('a.reference', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($lastArticle) {
            $lastRef = $lastArticle->getReference();
            $lastNum = (int)substr($lastRef, -5);
            $newNum = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNum = '00001';
        }

        return $prefix.$year.$newNum;
    }

    public function findAllWithFournisseur()
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.fournisseur', 'f')
            ->addSelect('f')
            ->leftJoin('a.createBy', 'c')
            ->addSelect('c')
            ->orderBy('a.reference', 'ASC')
            ->getQuery()
            ->getResult();
    }
public function getNextCounterValue(string $prefix, string $yearSuffix): int
{
    $lastReference = $this->createQueryBuilder('a')
        ->select('a.reference')
        ->where('a.reference LIKE :prefix')
        ->setParameter('prefix', $prefix.$yearSuffix.'%')
        ->orderBy('a.reference', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    
    if ($lastReference && preg_match('/\d{6}$/', $lastReference['reference'], $matches)) {
        return (int)$matches[0] + 1;
    }
    
    return 1; 
}
}