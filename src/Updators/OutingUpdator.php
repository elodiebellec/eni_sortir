<?php

namespace App\Updators;
use App\Entity\Outing;
use App\Entity\State;
use Doctrine\ORM\EntityManagerInterface;

class OutingUpdator
{


    private  $states;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->states = $this->entityManager->getRepository(State::class)->getStates();

    }

    /**
     * Update the state of an outing.
     * @param Outing $outing
     * @return Outing
     */
    public function updateState(Outing $outing):Outing{

        /**
         * If you remark a problem with the dates try to change your timezone in server config.
         */


        switch($outing->getState()){
            case $this->states['Créée']:
            {
                $this->created($outing);
                break;
            }
            case $this->states['Ouverte']:
            {
                $this->opened($outing);
                break;
            }
            case $this->states['Clôturée']:
            {
                $this->closed($outing);
                break;
            }
            case $this->states['Activité en cours']:
            {
                $this->inProgress($outing);
                break;
            }
            case $this->states['Activité passée']:
            {
                $this->finished($outing);
                break;
            }
            case $this->states['Activité annulée']:
            {
                $this->canceled($outing);
                break;
            }
        }

        return $outing;

    }

    private function created(Outing $outing)
    {
        if( $this->eventIsPublished())
        {
            $outing->setState($this->states['Ouverte']);
        }
    }

    private function opened(Outing $outing)
    {

        $participantsCount       = $outing->getParticipants()->count();
        $registrationsLimit      = $outing->getMaxRegistration();

        $dateRegistrationEnd     = $outing->getDateEnd();
        $now                     = new \DateTime;

        if($this->eventIsCancelled())
        {
            $outing->setState($this->states['Activité annulée']);

        }
        else if($this->registrationAreClosed($participantsCount,
                               $registrationsLimit,
                                $dateRegistrationEnd,
                                            $now))
        {
            $outing->setState($this->states['Clôturée']);
        }
    }

    private function closed(Outing $outing)
    {
        $participantsCount       = $outing->getParticipants()->count();
        $registrationsLimit      = $outing->getMaxRegistration();

        $dateEventBegin          = $outing->getDateBegin();
        $dateRegistrationEnd     = $outing->getDateEnd();
        $now                     = new \DateTime;
        $duration = $outing->getDuration();
        $dateEventEnd   = \DateTimeImmutable::createFromMutable($dateEventBegin)->modify("+ $duration day");

        if($this->eventIsCancelled())
        {
            $outing->setState($this->states['Activité annulée']);

        }
        else if($this->eventIsOngoing($dateEventBegin, $now, $dateEventEnd))
        {
            $outing->setState($this->states['Activité en cours']);
        }
        else if($this->registrationAreOpened($participantsCount,
                                    $registrationsLimit,
                                    $dateEventBegin,
                                    $now,
                                    $dateRegistrationEnd,
                                    $dateEventEnd))
        {
            $outing->setState($this->states['Ouverte']);
        }
    }

    private function inProgress(Outing $outing)
    {
        $dateEventBegin          = $outing->getDateBegin();
        $now                     = new \DateTime;
        $duration = $outing->getDuration();
        $dateEventEnd   = \DateTimeImmutable::createFromMutable($dateEventBegin)->modify("+ $duration day");

        if($this->eventIsFinished( $dateEventBegin, $now,  $dateEventEnd))
        {
            $outing->setState($this->states['Activité passée']);
        }
    }

    private function finished(Outing $outing)
    {
        $dateEventBegin          = $outing->getDateBegin();
        $now                     = new \DateTime;
        $currentState= $outing->getState()->getId();
        $duration = $outing->getDuration();
        $dateEventEnd   = \DateTimeImmutable::createFromMutable($dateEventBegin)->modify("+ $duration day");

        if($this->eventShallBeHistorized($dateEventBegin,  $now, $dateEventEnd, $currentState))
        {
            $outing->setState($this->states['Activité historisée']);
        }
    }

    private function canceled(Outing $outing)
    {
        $dateEventBegin          = $outing->getDateBegin();
        $now                     = new \DateTime;
        $currentState= $outing->getState()->getId();
        $duration = $outing->getDuration();
        $dateEventEnd   = \DateTimeImmutable::createFromMutable($dateEventBegin)->modify("+ $duration day");
       // $outing->setState($this->states['Activité historisée']);

        if($this->eventShallBeHistorized($dateEventBegin,  $now,  $dateEventEnd, $currentState))
        {
            $outing->setState($this->states['Activité historisée']);
        }
    }


    /**
     * @param \DateTimeInterface|null $dateEventBegin
     * @param \DateTime $now
     * @param \DateTimeImmutable $dateEventEnd
     * @return bool
     */
    private function eventIsFinished( \DateTimeInterface $dateEventBegin, \DateTime $now,  \DateTimeImmutable $dateEventEnd): bool
    {
        return $dateEventBegin < $now
            && $dateEventEnd < $now;
    }

    /**
     * @param int $participantsCount
     * @param int|null $registrationsLimit
     * @param \DateTimeInterface|null $dateRegistrationEnd
     * @param \DateTime $now
     * @return bool
     */
    private function registrationAreClosed(int $participantsCount,
                                           int $registrationsLimit,
                                           \DateTimeInterface $dateRegistrationEnd,
                                           \DateTime $now): bool
    {
        return $participantsCount >= $registrationsLimit
            || $dateRegistrationEnd < $now;
    }

    /**
     * @param \DateTimeInterface|null $dateEventBegin
     * @param \DateTime $now
     * @param \DateTimeImmutable $dateEventEnd
     * @return bool
     */
    private function eventIsOngoing( \DateTimeInterface $dateEventBegin,\DateTime $now, \DateTimeImmutable $dateEventEnd): bool
    {
        return $dateEventBegin <= $now
            && $dateEventEnd > $now;
    }

    /**
     * @param int $participantsCount
     * @param int|null $registrationsLimit
     * @param \DateTimeInterface|null $dateEventBegin
     * @param \DateTime $now
     * @param \DateTimeInterface|null $dateRegistrationEnd
     * @param \DateTimeImmutable $dateEventEnd
     * @return bool
     */
    private function registrationAreOpened(int $participantsCount, int $registrationsLimit,\DateTimeInterface $dateEventBegin, \DateTime $now, \DateTimeInterface $dateRegistrationEnd, \DateTimeImmutable $dateEventEnd): bool
    {
        return $participantsCount <= $registrationsLimit
            && $dateEventBegin > $now
            && $dateRegistrationEnd > $now
            && $dateEventEnd > $now;
    }


    /**
     * @param \DateTimeInterface|null $dateEventBegin
     * @param \DateTime $now
     * @param \DateTimeImmutable $dateEventEnd
     * @return bool
     */
    private function eventShallBeHistorized(\DateTimeInterface $dateEventBegin, \DateTime $now, \DateTimeImmutable $dateEventEnd, $currentState): bool
    {
        $isHistorized = false;

        $newDateEventBegin= \DateTimeImmutable::createFromMutable($dateEventBegin)->modify("+1 Month") ;
        if($currentState=== 5 && $dateEventEnd->modify("+1 Month") < $now)
        {
            $isHistorized= true;

        }

        elseif ($currentState=== 6 && $newDateEventBegin < $now)
        {
            $isHistorized= true;
        }

        return $isHistorized;
    }

    private function eventIsPublished():bool
    {
        return true;
    }

    private function eventIsCancelled(): bool
    {
        return false;
    }

}