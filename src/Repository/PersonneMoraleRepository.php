<?php

namespace App\Repository;

use App\Entity\PersonneMorale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonneMorale>
 */
class PersonneMoraleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonneMorale::class);
    }
    // In PersonneMoraleRepository
    public function findBySearch(string $search)
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :search OR p.RC LIKE :search OR p.identifiant_fiscal LIKE :search OR p.iCE LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    // src/Repository/PersonMoraleRepository.php

    public function findAllWithContractsCount(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p, COUNT(c.id) AS contractsCount')
            ->leftJoin('p.contrats', 'c') // Assuming 'contrats' is the property in PersonMorale entity
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

}
