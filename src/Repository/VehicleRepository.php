<?php

namespace App\Repository;

use App\Entity\Vehicle;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function save(Vehicle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vehicle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Vehicle[] Returns an array of Vehicle objects
     */
    public function findByFilters(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('v')
            ->orderBy('v.createdAt', 'DESC');

        $this->applyFilters($qb, $filters);

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countByFilters(array $filters = []): int
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)');

        $this->applyFilters($qb, $filters);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['type'])) {
            $qb->andWhere('v.type = :type')
                ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['brand'])) {
            $qb->andWhere('v.brand LIKE :brand')
                ->setParameter('brand', '%' . $filters['brand'] . '%');
        }

        if (!empty($filters['model'])) {
            $qb->andWhere('v.model LIKE :model')
                ->setParameter('model', '%' . $filters['model'] . '%');
        }

        if (!empty($filters['colour'])) {
            $qb->andWhere('v.colour LIKE :colour')
                ->setParameter('colour', '%' . $filters['colour'] . '%');
        }

        if (!empty($filters['price_min'])) {
            $qb->andWhere('v.price >= :price_min')
                ->setParameter('price_min', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $qb->andWhere('v.price <= :price_max')
                ->setParameter('price_max', $filters['price_max']);
        }

        if (!empty($filters['merchant'])) {
            $qb->andWhere('v.merchant = :merchant')
                ->setParameter('merchant', $filters['merchant']);
        }
    }

    /**
     * @return Vehicle[] Returns an array of Vehicle objects
     */
    public function findByMerchant(User $merchant): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.merchant = :merchant')
            ->setParameter('merchant', $merchant)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Vehicle[] Returns an array of Vehicle objects
     */
    public function findFollowedByUser(User $user): array
    {
        return $this->createQueryBuilder('v')
            ->join('v.followers', 'f')
            ->andWhere('f = :user')
            ->setParameter('user', $user)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return string[]
     */
    public function findDistinctBrands(): array
    {
        try {
            $result = $this->createQueryBuilder('v')
                ->select('DISTINCT v.brand')
                ->orderBy('v.brand', 'ASC')
                ->getQuery()
                ->getScalarResult();

            return array_column($result, 'brand');
        } catch (\Exception $e) {
            // Return empty array if database is empty or not accessible
            return [];
        }
    }

    /**
     * @return string[]
     */
    public function findDistinctColours(): array
    {
        try {
            $result = $this->createQueryBuilder('v')
                ->select('DISTINCT v.colour')
                ->orderBy('v.colour', 'ASC')
                ->getQuery()
                ->getScalarResult();

            return array_column($result, 'colour');
        } catch (\Exception $e) {
            // Return empty array if database is empty or not accessible
            return [];
        }
    }

    /**
     * @return string[]
     */
    public function findDistinctModels(): array
    {
        try {
            $result = $this->createQueryBuilder('v')
                ->select('DISTINCT v.model')
                ->orderBy('v.model', 'ASC')
                ->getQuery()
                ->getScalarResult();

            return array_column($result, 'model');
        } catch (\Exception $e) {
            // Return empty array if database is empty or not accessible
            return [];
        }
    }

    /**
     * @return Vehicle[] Returns an array of Vehicle objects with valid merchants
     */
    public function findAllWithValidMerchants(): array
    {
        try {
            return $this->createQueryBuilder('v')
                ->leftJoin('v.merchant', 'm')
                ->where('m.id IS NOT NULL')
                ->orderBy('v.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            // If there's an error, try to get vehicles without the merchant join
            // This handles cases where there might be orphaned vehicle records
            try {
                return $this->createQueryBuilder('v')
                    ->orderBy('v.createdAt', 'DESC')
                    ->getQuery()
                    ->getResult();
            } catch (\Exception $e2) {
                // If all else fails, return empty array
                return [];
            }
        }
    }
}
