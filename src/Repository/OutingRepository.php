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


    public function findAllOutings($page, $name, $dateBegin, $dateEnd, $site, $isPlanner, $isRegistered, $isNotRegistered, $isOutDated,$user )
    {
        $idUser = $user->getId();
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

        if(!is_null ($name))
        {
            $queryBuilder->andWhere("o.name LIKE '%$name%'");
        }

        if(!is_null ($dateBegin))
        {
            $queryBuilder->andWhere("o.dateBegin = $dateBegin");
        }

        if(!is_null ($dateEnd))
        {
            $queryBuilder->andWhere("o.dateEnd = $dateEnd");
        }
        if(!is_null ($site))
        {
            $queryBuilder->andWhere("o.site = $site");
        }

        if(!is_null ($isPlanner))
        {
            $queryBuilder->andWhere("o.planner = $idUser");
        }

        $query = $queryBuilder->getQuery();

        $offset = ($page -1) *10;
        $query->setFirstResult($offset);
        $query->setMaxResults(10);

        $paginator = new Paginator($query);

       return  $paginator;

    }

   /* public function findByFilter($page, $name, $dateBegin, $dateEnd, $site, $isPlanner, $isRegistered, $isNotRegistered, $isOutDated,$user )
    {

        $idUser = $user->getId();
        $queryBuilder = $this->createQueryBuilder('f');

        if(!is_null ($name))
        {
            $queryBuilder->andWhere("f.name LIKE '%$name%'");
        }

        if(!is_null ($dateBegin))
        {
            $queryBuilder->andWhere("f.dateBegin = $dateBegin");
        }

        if(!is_null ($dateEnd))
        {
            $queryBuilder->andWhere("f.dateEnd = $dateEnd");
        }
        if(!is_null ($site))
        {
            $queryBuilder->andWhere("f.site = $site");
        }

        if(!is_null ($isPlanner))
        {
           $queryBuilder->andWhere("f.planner = $idUser");
        }


        if(!is_null ($isRegistered))
        {

        }

        if(!is_null ($isNotRegistered))
        {

        }

        if(!is_null ($isOutDated))
        {

        }


        $queryBuilder->leftJoin('f.state','state');
        $queryBuilder->leftJoin('f.planner','planner');
        $queryBuilder->leftJoin('f.participants','participants');
        $queryBuilder->leftJoin('f.site','site');
        $queryBuilder->leftJoin('f.location','location');

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

    } */

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
