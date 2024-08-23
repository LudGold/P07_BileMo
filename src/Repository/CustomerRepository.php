<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\User;
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

    public function findallWithPagination(User $user, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->addSelect('u') // Fetch the related user entity eagerly
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->setFirstResult(($page - 1) * $limit)
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
