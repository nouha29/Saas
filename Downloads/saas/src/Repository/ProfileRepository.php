<?php

namespace App\Repository;

use App\Entity\Profiles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Profiles>
 *
 * @method Profiles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profiles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profiles[]    findAll()
 * @method Profiles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profiles::class);
    }

    public function save(Profiles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Profiles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByIntitule(string $intitule): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.intitule LIKE :intitule')
            ->setParameter('intitule', '%' . $intitule . '%')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
