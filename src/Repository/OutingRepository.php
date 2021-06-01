<?php

namespace App\Repository;

use App\Entity\Outing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Outing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Outing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Outing[]    findAll()
 * @method Outing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OutingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outing::class);
    }

    public function findAllOutings($page)
    {
        $queryBuilder = $this->createQueryBuilder('o');

        $queryBuilder->leftJoin('o.state','state');
        $queryBuilder->leftJoin('o.planner','planner');
        $queryBuilder->leftJoin('o.participants','participants');
        $queryBuilder->leftJoin('o.site','site');
        $queryBuilder->leftJoin('o.location','location');

        $queryBuilder->addSelect('state');
        $queryBuilder->addSelect('planner');
        $queryBuilder->addSelect('participants');
        $queryBuilder->addSelect('site');
        $queryBuilder->addSelect('location');

        $query = $queryBuilder->getQuery();

        $offset = ($page -1) *10;
        $query->setFirstResult($offset);
        $query->setMaxResults(10);

        $paginator = new Paginator($query);

       return  $paginator;

    }

    // /**
    //  * @return Outing[] Returns an array of Outing objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Outing
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
