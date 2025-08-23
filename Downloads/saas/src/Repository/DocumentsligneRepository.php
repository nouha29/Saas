<?php

namespace App\Repository;

use App\Entity\Documentslignes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Documentslignes>
 */
class DocumentsligneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Documentslignes::class);
    }

    public function save(Documentslignes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Documentslignes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByDocumentId(int $documentId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id_document = :val')
            ->setParameter('val', $documentId)
            ->getQuery()
            ->getResult();
    }
}
