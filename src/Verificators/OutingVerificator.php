<?php

namespace App\Verificators;
use App\Entity\Outing;
use App\Entity\Participant;

/**
 * Class OutingVerificator
 * @package App\Verificators
 */
class OutingVerificator
{
    private array $errors;
    private Outing $outing;

    public function __construct()
    {
        $this->errors = [];
    }

    /**
     * Get the different errors following the verification of the outing.
     * @return string
     */
    public function getErrorMessages():string{
        $flash = "";
        foreach($this->errors as $error){
            $flash .= $error." ";
        }
        return $flash;
    }

    /**
     * @param Participant $participant
     * @param $outing
     * @return bool
     */
    public function canAdd(Participant $participant, $outing) :bool {

        if($this->outingIsNull($outing)){
            return false;
        }

        $this->outing = $outing;

        $this->checkParticipantIsPlanner($participant);
        $this->checkHasReachRegistrationLimit();
        $this->checkIsClosed();
        $this->checkParticipantAlreadyRegistered($participant);

        return $this->hasSucceed();
    }

    private function checkParticipantAlreadyRegistered(Participant $participant): void
    {
        $participantAlreadyRegistered = $participant->getOutings()->contains($this->outing);
        if($participantAlreadyRegistered){
            $this->errors[] = OutingErrors::$participantAlreadyRegistered;
        }
    }
    private function checkHasReachRegistrationLimit(): void
    {
        $hasReachRegistrationLimit = $this->outing->getParticipants()->count() >= $this->outing->getMaxRegistration();
        if($hasReachRegistrationLimit){
            $this->errors[] = OutingErrors::$hasReachRegistrationLimit;

        }
    }
    private function checkParticipantIsPlanner(Participant $participant): void
    {
        $participantIsPlanner = strcmp($this->outing->getPlanner()->getPseudo(), $participant->getPseudo()) === 0;
        if($participantIsPlanner){
            $this->errors[] = OutingErrors::$participantToAddIsPlanner;
        }
    }
    private function checkIsClosed(): void
    {
        $isClosed = $this->outing->getState()->getLabel() != 'Ouverte';
        if($isClosed){
            $this->errors[] = OutingErrors::$isClosed;
        }
    }

    /**
     * @param Participant $participant
     * @param $outing
     * @return bool
     */
    public function canRemove(Participant $participant, $outing) :bool {

        if($this->outingIsNull($outing)){
            return false;
        }

        $this->outing = $outing;

        $this->checkParticipantIsNotRegistered($participant);
        $this->checkParticipantToRemoveIsPlanner($participant);
        $this->checkOutingIsNotActive();

        return $this->hasSucceed();
    }

    private function checkParticipantIsNotRegistered(Participant $participant): void
    {
        $participantIsNotRegistered = !$participant->getOutings()->contains($this->outing);
        if($participantIsNotRegistered){
            $this->errors[] = OutingErrors::$participantIsNotRegistered;
        }
    }
    private function checkParticipantToRemoveIsPlanner(Participant $participant): void
    {
        $participantToRemoveIsPlanner = $this->outing->getPlanner()->getPseudo() === $participant->getPseudo();
        if($participantToRemoveIsPlanner){
            $this->errors[] = OutingErrors::$participantToRemoveIsPlanner;
        }
    }

    private function checkOutingIsNotActive(): void
    {
        $outingIsNotActive =    $this->outing->getState()->getLabel() === 'Activité passée'
                                || $this->outing->getState()->getLabel() === 'Activité annulée';
        if($outingIsNotActive){
            $this->errors[] = OutingErrors::$outingIsNotActive;
        }
    }
    private function outingIsNull($outing): bool
    {
        $outingIsNull= $outing === null;
        if($outingIsNull){
            $this->errors[] = OutingErrors::$outingIsNull;
        }
        return $outingIsNull;
    }

    /**
     * @return bool
     */
    private function hasSucceed():bool{
        return count($this->errors) === 0;
    }
}
