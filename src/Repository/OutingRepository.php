<?php

namespace App\Repository;

use App\Entity\Outing;
use App\Model\OutingsFilter;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
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

        $tabNotRegistered = $this->findNotRegistered($user);
        $tableRegistered = $this->findRegistered($user);



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



       // $queryBuilder->andWhere("state.label =:created  and planner.id <> $idUser");
        //$queryBuilder->setParameter('created', 'Créée');

       $queryBuilder->andWhere('state.label <> :historized');
       $queryBuilder->setParameter('historized', 'Activité historisée');

       //die($queryBuilder->getDql());
      // $queryBuilder->setParameters( new ArrayCollection(array( new Parameter('created',$stateLabel), new Parameter('idUser', $idUser))));

        /**
         * @var  OutingsFilter $filter
         */


       if($filter->getName())
        {
            $queryBuilder->andWhere("o.name LIKE :name");
            $queryBuilder->setParameter('name', '%'.$filter->getName().'%');
        }


        if($filter->getDateBeginFilter())
        {
            $queryBuilder->andWhere("o.dateBegin >= :dateBeginFilter");
            $queryBuilder->setParameter('dateBeginFilter', $filter->getDateBeginFilter());
        }



        if($filter->getDateEndFilter())
        {
            $queryBuilder->andWhere("o.dateBegin<= :dateEndFilter");
            $queryBuilder->setParameter('dateEndFilter', $filter->getDateEndFilter());
        }



        if( $filter->getSite())
        {
            $queryBuilder->andWhere("site.name = :site");
            $queryBuilder->setParameter('site', $filter->getSite());
        }

        if( $filter->getIsPlanner())
        {

            $queryBuilder->andWhere("planner.id = :idUser");
            $queryBuilder->setParameter('idUser', $idUser);
        }

        if($filter->getIsRegistered())
        {

            if($filter->getIsNotRegistered())
            {
                $queryBuilder->orWhere("o IN (:tableRegistered)");
                $queryBuilder->setParameter('tableRegistered', $tableRegistered);
            }

            else{
                $queryBuilder->andWhere("o IN (:tableRegistered)");
                $queryBuilder->setParameter('tableRegistered', $tableRegistered);

            }
        }

         if($filter->getIsNotRegistered())
         {

             if(($filter->getIsRegistered()))
            {
                    $queryBuilder->orWhere("o IN (:tabNotRegistered)");
                     $queryBuilder->setParameter('tabNotRegistered', $tabNotRegistered);
            }

             else{
                 $queryBuilder->andWhere("o IN (:tabNotRegistered)");
                 $queryBuilder->setParameter('tabNotRegistered', $tabNotRegistered);

             }
         }


        if($filter->getIsOutDated())
        {
            $queryBuilder->andWhere("state.label = :state");
            $queryBuilder->setParameter('state', $filter->getIsOutDated());
        }


        $query = $queryBuilder->getQuery();
        $offset = ($page -1) *10;
        $query->setFirstResult($offset);
        $query->setMaxResults(10);

        $paginator = new Paginator($query);

        return $paginator;

    }

    public function findNotRegistered($user)
    {
        /**
         * find all outings
         */
        $tabAll= $this->findAll();


        /**
         * find only user's outings
         */
        $tabRegistered= $this->findRegistered($user);

        /**
         * This array will contains outings that the user is not registered to
         */

        $tabNotRegistered=array();



        for ($i=0; $i<sizeof($tabAll); $i++)
        {
            $belongToRegistered = false;

            for ($j=0; $j<sizeof($tabRegistered); $j++)
            {
               if($tabAll[$i]->getid() === $tabRegistered[$j]->getId())
               {
                    $belongToRegistered = true;
               }

            }

            if(!$belongToRegistered)
            {

                    $tabNotRegistered[]=$tabAll[$i];

            }
        }


        return  $tabNotRegistered;

    }

    public function findRegistered($user)
    {

        $tabUser[]= $user;
        $queryBuilder = $this->createQueryBuilder('o');
        $queryBuilder->leftJoin('o.participants','p');
        $queryBuilder->addSelect('p');


        $queryBuilder->andWhere("p IN (:user)");
        $queryBuilder->setParameter('user', $tabUser);
        $query = $queryBuilder->getQuery();

        return $query->getResult();

    }

    public function findAllForStateUpdate()
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

        $queryBuilder->andWhere('state.label <> :historized');
        $queryBuilder->setParameter('historized', 'Activité historisée');

        $query = $queryBuilder->getQuery();

        return  $query->getResult() ;


    }





}
