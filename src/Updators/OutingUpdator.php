<?php

namespace App\Updators;
use App\Entity\Outing;
use App\Entity\State;
use Doctrine\ORM\EntityManagerInterface;

class OutingUpdator
{

    /**
     * @var array
     */
    private array $states;
    private EntityManagerInterface $entityManager;

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
         * Default timezone was 2 hours too early.
         * But every date in the db is 2 hours early so setting the right time break the comparisons
         * solution : get the right timezone for every date in database then uncomment this line.
         * TODO:Make this work goddamn
         */
        /*date_default_timezone_set('Europe/Paris');*/

        if($outing->getState()->getLabel() === 'Activité annulée') return $outing;

        $states = $this->states;

        $participantsCount       = $outing->getParticipants()->count();
        $registrationsLimit      = $outing->getMaxRegistration();

        $dateEventBegin          = $outing->getDateBegin();
        $dateRegistrationEnd     = $outing->getDateEnd();
        $now                     = new \DateTime;

        $dateEventEnd   = \DateTimeImmutable::createFromMutable($dateEventBegin)
            ->modify("+ {$outing->getDuration()} hours");

        switch(true){

            case $this->eventIsFinished($dateEventBegin, $now, $dateEventEnd):
                $outing->setState($states['Activité passée']);
                break;


            case $this->registrationAreClosed($participantsCount, $registrationsLimit,
                                              $dateRegistrationEnd,$now):
                $outing->setState($states['Clôturée']);
                break;


            case $this->eventIsOngoing($dateEventBegin, $now, $dateEventEnd) :
                $outing->setState($states['Activité en cours']);
                break;


            case $this->registrationAreOpened($participantsCount, $registrationsLimit,
                            $dateEventBegin, $now, $dateRegistrationEnd, $dateEventEnd):
                $outing->setState($states['Ouverte']);
                break;
        }

        return $outing;
    }

    /**
     * @param \DateTimeInterface|null $dateEventBegin
     * @param \DateTime $now
     * @param \DateTimeImmutable $dateEventEnd
     * @return bool
     */
    private function eventIsFinished(?\DateTimeInterface $dateEventBegin, \DateTime $now, \DateTimeImmutable $dateEventEnd): bool
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
                                           ?int $registrationsLimit,
                                           ?\DateTimeInterface $dateRegistrationEnd,
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
    private function eventIsOngoing(?\DateTimeInterface $dateEventBegin, \DateTime $now, \DateTimeImmutable $dateEventEnd): bool
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
    private function registrationAreOpened(int $participantsCount, ?int $registrationsLimit, ?\DateTimeInterface $dateEventBegin, \DateTime $now, ?\DateTimeInterface $dateRegistrationEnd, \DateTimeImmutable $dateEventEnd): bool
    {
        return $participantsCount <= $registrationsLimit
            && $dateEventBegin > $now
            && $dateRegistrationEnd > $now
            && $dateEventEnd > $now;
    }
}