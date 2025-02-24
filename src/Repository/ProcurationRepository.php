<?php

namespace App\Repository;

use App\Entity\Procuration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Procuration>
 */
class ProcurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Procuration::class);
    }

    public function countByMonth(int $year): array
    {
        $monthlyData = array_fill(1, 12, 0); // Initialize an array with 12 months

        // Loop through each month to count entries
        for ($month = 1; $month <= 12; $month++) {
            $startDate = new \DateTime("$year-$month-01");
            $endDate = (clone $startDate)->modify('first day of next month');

            $qb = $this->createQueryBuilder('d');
            $qb->select('COUNT(d.id) as count')
                ->where('d.createdAt >= :start')
                ->andWhere('d.createdAt < :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate);

            $result = $qb->getQuery()->getSingleScalarResult();
            $monthlyData[$month] = (int) $result; // Store the count for the current month
        }

        return array_values($monthlyData); // Return values indexed 0-11 for the frontend
    }

    public function searchProcurations(?string $searchQuery = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.persons', 'ps')
            ->addSelect('ps');

        // Filter by search query if provided
        if (!empty($searchQuery)) {
            $qb->andWhere('ps.last_name IS NULL OR ps.last_name  LIKE :searchQuery')
                ->setParameter('searchQuery', "{$searchQuery}%");
        }

        return $qb->getQuery()->getResult();
    }

}
