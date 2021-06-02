<?php

namespace App\Updators;
use App\Entity\Outing;
use App\Entity\State;
use Doctrine\ORM\EntityManagerInterface;

class OutingUpdator
{

    private array $states;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->states = $this->entityManager->getRepository(State::class)->getStates();

    }

    public function updateState(Outing $outing):Outing{

        if($outing->getState()->getLabel() === 'Activité annulée') return $outing;

        $states = $this->states;

        $outingParticipantsCount    = $outing->getParticipants()->count();
        $outingMaxRegistration      = $outing->getMaxRegistration();

        $dateBegin  = $outing->getDateBegin();
        $dateEnd    = $outing->getDateEnd();
        $now        = new \DateTime;

        switch(true){
            case $outingParticipantsCount >= $outingMaxRegistration :
                $outing->setState($states['Clôturée']);
                break;
            case $outingParticipantsCount <= $outingMaxRegistration && $dateBegin > $now :
                $outing->setState($states['Ouverte']);
                break;
            case $dateBegin < $now && $dateEnd > $now :
                $outing->setState($states['Activité en cours']);
                break;
            case $dateEnd < $now :
                $outing->setState($states['Activité passée']);
                break;
        }

        return $outing;
    }
}