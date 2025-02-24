<?php

namespace App\Repository;

use App\Entity\PersonnePhysique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonnePhysique>
 */
class PersonnePhysiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonnePhysique::class);
    }
    public function findBySearch(string $search): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.first_name LIKE :search OR p.last_name LIKE :search OR p.cin LIKE :search OR p.birth_place LIKE :search' )
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    public function searchClients(?string $searchQuery)
    {
        $qb = $this->createQueryBuilder('c');

        // Filter by search query (search in name or email)
        if (!empty($searchQuery)) {
            $qb->andWhere('c.last_name LIKE :search')
                ->setParameter('search', "{$searchQuery}%");
        }

        return $qb->getQuery()->getResult();
    }

    public function searchPersonPhysiqueWithRelations(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.contrats', 'c')
            ->addSelect('c')
            ->leftJoin('p.procurations', 'pr')
            ->addSelect('pr')
            ->leftJoin('p.desistements', 'd')
            ->addSelect('d')
            ->where('p.last_name LIKE :search')
            ->setParameter('search', "{$searchTerm}%")
            ->getQuery()
            ->getResult();
    }
}
