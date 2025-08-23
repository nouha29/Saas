<?php

namespace App\Repository;

use App\Entity\Documents;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Documents>
 *
 * @method Documents|null find($id, $lockMode = null, $lockVersion = null)
 * @method Documents|null findOneBy(array $criteria, array $orderBy = null)
 * @method Documents[]    findAll()
 * @method Documents[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Documents::class);
    }

    public function save(Documents $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Documents $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getNextReference(string $type): string
    {
        $prefixMap = [
            'Devis achat' => 'DA',
            'Commande achat' => 'CA',
            'Facture achat' => 'FA',
            'Facture achat avoire' => 'FAA',
            'Bon d\'entré' => 'BE',
            'Bon de transfert' => 'BT',
            'Bon de retour' => 'BR',
            'Devis vente' => 'DV',
            'Commande vente' => 'CV',
            'Facture vente' => 'FV',
            'Facture vente avoire' => 'FVA',
            'Bon de sortie' => 'BS',
            'Bon de livraison' => 'BL',
            'Inventaire' => 'Inv'
        ];

        $prefix = $prefixMap[$type] ?? 'DOC';
        $year = date('y');
        $lastNum = $this->getLastReferenceNumber($prefix . $year);

        return sprintf('%s%06d', $prefix . $year, $lastNum + 1);
    }

    private function getLastReferenceNumber(string $prefix): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('MAX(SUBSTRING(d.reference, 5)) as max_num')
            ->where('d.reference LIKE :prefix')
            ->setParameter('prefix', $prefix . '%')
            ->getQuery();

        $result = $qb->getSingleScalarResult();

        return $result ? (int)$result : 0;
    }
    public function findWithLignes(int $id): ?Documents
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.lignes', 'l')
            ->addSelect('l')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}
