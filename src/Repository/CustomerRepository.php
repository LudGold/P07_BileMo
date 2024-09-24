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
    }
}
