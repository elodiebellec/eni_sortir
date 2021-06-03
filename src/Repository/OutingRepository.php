<?php

namespace App\Repository;

use App\Entity\Outing;
use App\Model\OutingsFilter;

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


    public function findAllOutings($page, $filter,$user )
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

        /**
         * @var  OutingsFilter $filter
         */
        if(!is_null ($filter->getName()))
        {
            $queryBuilder->andWhere("o.name LIKE :name");
            $queryBuilder->setParameter('name', '%'.$filter->getName().'%');
        }


        if(!is_null ($filter->getDateBeginFilter()))
        {
            $queryBuilder->andWhere("o.dateBegin >= :dateBeginFilter");
            $queryBuilder->setParameter('dateBeginFilter', $filter->getDateBeginFilter());
        }



        if(!is_null ($filter->getDateEndFilter()))
        {
            $queryBuilder->andWhere("o.dateBegin<= :dateEndFilter");
            $queryBuilder->setParameter('dateEndFilter', $filter->getDateEndFilter());
        }



        if(!is_null ($filter->getSite()))
        {
            $queryBuilder->andWhere("site.name = :site");
            $queryBuilder->setParameter('site', $filter->getSite());
        }



        if( ($filter->getIsPlanner()))
        {

            $queryBuilder->andWhere("planner.id = :idUser");
           $queryBuilder->setParameter('idUser', $idUser);
        }

        if(($filter->getIsRegistered()))
        {
            // $queryBuilder->andWhere("o.planner = $idUser");
           $queryBuilder->andWhere("participants.id = :idUser");
            $queryBuilder->setParameter('idUser', $idUser);
        }
        if(($filter->getIsNotRegistered()))
        {

            $queryBuilder->andWhere("participants.id != :idUser");
            $queryBuilder->setParameter('idUser', $idUser);
        }

        if(($filter->getIsOutDated()))
        {

            $queryBuilder->andWhere("state.label = :state");
            $queryBuilder->setParameter('state', $filter->getIsOutDated());
        }
        /*
         * Count of the number of results
         */
        $queryBuilder->select('COUNT( distinct o)');
        $countQuery= $queryBuilder->getQuery();
        $maxOutings = $countQuery->getSingleScalarResult();


        /*
         * New selection of results
         */
        $queryBuilder->select('o');
        $query = $queryBuilder->getQuery();

        $offset = ($page -1) *10;
        $query->setFirstResult($offset);
        $query->setMaxResults(10);

       $paginator = new Paginator($query);

        // 'outings'=>$query->getResult()



       return  [
           'outings'=>$paginator,
           'maxOutings'=>$maxOutings

       ];

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
