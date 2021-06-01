<?php

namespace App\Repository;

use App\Entity\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method State|null find($id, $lockMode = null, $lockVersion = null)
 * @method State|null findOneBy(array $criteria, array $orderBy = null)
 * @method State[]    findAll()
 * @method State[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, State::class);
    }


    public function getStates()
    {
        $states = [];
        foreach($this->findAll() as $state){
            $states[$state->getLabel()] = $state;
        }
        return $states;
    }

    /*private function getState(string $label,StateRepository $stateRepository ):State{
        $states = $stateRepository->findAll();
        foreach($states as $state) {
            if ($state->getLabel() === $label) {
                return $state;

            }
        }
        dd('ERROR state not found');
    }*/

    /*
    public function findOneBySomeField($value): ?State
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
