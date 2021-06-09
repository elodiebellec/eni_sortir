<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }


    public function findCityByNameWithLocations($name)
    {
        return $this
            ->createQueryBuilder('c')
            ->leftJoin('c.locations','locations')
            ->addSelect('locations')
            ->andWhere('c.name = :name')
            ->setParameter('name', $name)
            ->orderBy('locations.name')
            ->getQuery()
            ->setMaxResults(10)
            ->getResult()
            ;
    }
}
