<?php

namespace App\Repository;

use App\Entity\Contrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }
    public function findCompromis(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.type = :type')
            ->setParameter('type', 'compromis')
            ->getQuery()
            ->getResult();
    }

    public function findVentes(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.type = :type')
            ->setParameter('type', 'vente')
            ->getQuery()
            ->getResult();
    }

    public function countByMonth(int $year, string $type): array
    {
        $monthlyData = array_fill(1, 12, 0); // Initialize an array with 12 months

        // Loop through each month to count entries
        for ($month = 1; $month <= 12; $month++) {
            $startDate = new \DateTime("$year-$month-01");
            $endDate = (clone $startDate)->modify('first day of next month');

            $qb = $this->createQueryBuilder('c');
            $qb->select('COUNT(c.id) as count')
                ->where('c.createdAt >= :start')
                ->andWhere('c.createdAt < :end')
                ->andWhere('c.type = :type') // Filter by type
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->setParameter('type', $type);

            $result = $qb->getQuery()->getSingleScalarResult();
            $monthlyData[$month] = (int) $result; // Store the count for the current month
        }

        return array_values($monthlyData); // Return values indexed 0-11 for the frontend
    }

//    public function findLastVente(): ?Contrat
//    {
//        return $this->createQueryBuilder('v')
//            ->orderBy('v.repertoir', 'DESC')
//            ->setMaxResults(1)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }

    public function searchContrat(?string $searchQuery = null, ?string $PmoraleName = null,string $type): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.type = :type')
            ->setParameter('type', $type)
            ->leftJoin('c.pmorale', 'pm') // Assuming 'pmorale' is the relation in Contrat
            ->addSelect('pm')
            ->leftJoin('c.pphysique', 'pp') // Assuming 'pPhysiques' is the relation in Contrat
            ->addSelect('pp');

        // Filter by search query if provided
        if (!empty($searchQuery)) {
            $qb->andWhere('pp.last_name IS NULL OR pp.last_name  LIKE :searchQuery')
                ->setParameter('searchQuery', "{$searchQuery}%");
        }

        // Filter by PersonneMorale if provided
        if (!empty($PmoraleName)) {
            $qb->andWhere('pm.name = :PmoraleName')
                ->setParameter('PmoraleName', $PmoraleName);
        }

        return $qb->getQuery()->getResult();
    }

}
