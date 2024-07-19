<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findallWithPagination($page, $limit)
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->addSelect('u') // Fetch the related user entity eagerly
            ->setFirstResult((($page - 1) * $limit))
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
        //    /**
        //     * @return Customer[] Returns an array of Customer objects
        //     */
        //    public function findByExampleField($value): array
        //    {
        //        return $this->createQueryBuilder('c')
        //            ->andWhere('c.exampleField = :val')
        //            ->setParameter('val', $value)
        //            ->orderBy('c.id', 'ASC')
        //            ->setMaxResults(10)
        //            ->getQuery()
        //            ->getResult()
        //        ;
        //    }

        //    public function findOneBySomeField($value): ?Customer
        //    {
        //        return $this->createQueryBuilder('c')
        //            ->andWhere('c.exampleField = :val')
        //            ->setParameter('val', $value)
        //            ->getQuery()
        //            ->getOneOrNullResult()
        //        ;
        //    }

    }
}
