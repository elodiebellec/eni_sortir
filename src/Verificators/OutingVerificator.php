<?php

namespace App\Verificators;
use App\Entity\Outing;
use App\Entity\Participant;

class OutingVerificator
{

    private array $errors;
    private Outing $outing;

    public function __construct()
    {
        $this->errors = [];
    }
    public function getErrorMessages():string{
        $flash = "";
        foreach($this->errors as $error){
            $flash .= $error." ";
        }
        return $flash;
    }
    public function canAdd(Participant $participant, $outing) :bool {

        if($this->checkOutingDoNotExists($outing)){
            return $this->hasErrors();
        }

        $this->outing = $outing;

        $this->checkParticipantIsPlanner($participant);
        $this->checkHasReachRegistrationLimit();
        $this->checkIsClosed();
        $this->checkParticipantAlreadyRegistered($participant);

        return $this->hasErrors();
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

    public function canRemove(Participant $participant, $outing) :bool {

        if($this->checkOutingDoNotExists($outing)){
            return $this->hasErrors();
        }

        $this->outing = $outing;

        $this->checkParticipantIsNotRegistered($participant);
        $this->checkParticipantToRemoveIsPlanner($participant);
        $this->checkOutingIsNotActive();

        return $this->hasErrors();
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
    private function checkOutingDoNotExists($outing): bool
    {
        $outingDoNotExists = $outing === null;
        if($outingDoNotExists){
            $this->errors[] = OutingErrors::$outingDoNotExists;
        }
        return $outingDoNotExists;
    }
    private function hasErrors():bool{
        return count($this->errors) === 0;
    }
}
