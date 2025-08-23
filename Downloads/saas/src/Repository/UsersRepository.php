<?php
namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }
public function loadUserByIdentifier(string $identifier): ?Users
    {
        return $this->createQueryBuilder('u')
            ->where('u.mail = :mail')
            ->setParameter('mail', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findOneByEmail(string $email): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.mail = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findByProfileTitle(string $profileTitle): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.profile', 'p')
            ->where('p.intitule = :title')
            ->setParameter('title', $profileTitle)
            ->getQuery()
            ->getResult();
    }
    public function findByProfile(string $profileIntitule): array
{
    return $this->createQueryBuilder('u')
        ->join('u.profile', 'p')
        ->where('p.intitule = :intitule')
        ->setParameter('intitule', $profileIntitule)
        ->getQuery()
        ->getResult();
}


}