<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    public function searchUsers(?string $searchQuery, ?string $role)
    {
        $qb = $this->createQueryBuilder('u');

        // Filter by search query (search in name or email)
        if (!empty($searchQuery)) {
            $qb->andWhere('u.username LIKE :search')
                ->setParameter('search', "{$searchQuery}%");
        }

        // Filter by user type
        if (!empty($role)) {
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role','%"'. $role .'"%');
        }

        return $qb->getQuery()->getResult();
    }

}
