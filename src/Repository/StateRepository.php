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

    /**
     * StateRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, State::class);
    }

    /**
     * Get all states that are registered in the database as an array.
     * Array is indexed with states labels.
     * You can get a specific states by specifying the label as index.
     * Example :
     *      $states = $stateRepo->getStates();
     *      $openedState = $states['Ouverte'];
     * @return array
     */
    public function getStates() :array
    {
        $states = [];
        foreach($this->findAll() as $state){
            $states[$state->getLabel()] = $state;
        }
        return $states;
    }

    /**
     * Get specific state that is registered in the database.
     * Specify the label of the desired state.
     * Example :
     *      $openendState = $stateRepo->getState('Ouverte');
     * @param string $label
     * @return State
     */
    public function getState(string $label):State{
        $states = $this->findAll();
        foreach($states as $state) {
            if ($state->getLabel() === $label) {
                return $state;

            }
        }
        dd('ERROR state not found');
    }
}
