<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product, bool $flush = false): void
    {
        $this->getEntityManager()->persist($product);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $product, bool $flush = false): void
    {
        $this->getEntityManager()->remove($product);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findByAvailability(int $minCount = 1): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.count >= :minCount')
            ->setParameter('minCount', $minCount)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('p');

        if (isset($filters['search']) && $filters['search']) {
            $qb->andWhere('p.title LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (isset($filters['categories']) && !empty($filters['categories'])) {
            $qb->join('p.categories', 'c')
               ->andWhere('c.id IN (:categories)')
               ->setParameter('categories', $filters['categories']);
        }

        if (isset($filters['tags']) && !empty($filters['tags'])) {
            $qb->join('p.tags', 't')
               ->andWhere('t.id IN (:tags)')
               ->setParameter('tags', $filters['tags']);
        }

        if (isset($filters['minCount'])) {
            $qb->andWhere('p.count >= :minCount')
               ->setParameter('minCount', $filters['minCount']);
        }

        if (isset($filters['orderBy']) && isset($filters['orderDir'])) {
            $qb->orderBy('p.' . $filters['orderBy'], $filters['orderDir']);
        } else {
            $qb->orderBy('p.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}