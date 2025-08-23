<?php

namespace App\Repository;

use App\Entity\ProfileFunctions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProfileFunctionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfileFunctions::class);
    }

    public function hasPermission(int $profileId, string $functionName): bool
    {
        $result = $this->createQueryBuilder('pf')
            ->join('pf.id_function', 'f')
            ->where('pf.proflie = :profileId')
            ->andWhere('f.intitule = :functionName')
            ->setParameter('profileId', $profileId)
            ->setParameter('functionName', $functionName)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}