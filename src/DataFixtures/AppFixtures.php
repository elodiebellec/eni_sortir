<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Location;
use App\Entity\Outing;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;


class AppFixtures extends Fixture
{


    public function load(ObjectManager $manager)
    {
        $generator = Faker\Factory::create('fr_FR');

        $this->defineStates($manager);

      //  $this->generateCities($manager, $generator);

      //  $this->generateLocation($manager, $generator);

      //  $this->generateSites($manager, $generator);

       // $this->generateParticipants($manager, $generator);

       // $this->generateOutings($manager, $generator);
    }

    private function defineStates(ObjectManager $manager): void
    {
        $created    = new State;
        $opened     = new State;
        $fenced     = new State;
        $inProgress = new State;
        $finished   = new State;
        $canceled   = new State;
        $historized = new State;

        $created->setLabel("Créée");
        $opened->setLabel("Ouverte");
        $fenced->setLabel("Clôturée");
        $inProgress->setLabel("Activité en cours");
        $finished->setLabel("Activité passée");
        $canceled->setLabel("Activité annulée");
        $historized->setLabel("Activité historisée");

        $manager->persist($created);
        $manager->persist($opened);
        $manager->persist($fenced);
        $manager->persist($inProgress);
        $manager->persist($finished);
        $manager->persist($canceled);
        $manager->persist($historized);

        $manager->flush();
    }


    private function generateCities(ObjectManager $manager,
                                    Faker\Generator $generator,
                                    int $count = 50): void
    {
        for ($i = 0; $i < $count; $i++) {
            $city = new City;
            $city
                ->setName($generator->city)
                ->setPostalCode($generator->postcode);
            $manager->persist($city);
        }

        $manager->flush();
    }

    private function generateLocation(ObjectManager $manager,
                                      Faker\Generator $generator,
                                      int $count = 80): void
    {
        $cities = $this->getEntities($manager, City::class);

        for ($i = 0; $i < $count; $i++) {

            $location = new Location;

            $location
                ->setName($generator->company)
                ->setStreet($generator->streetAddress)
                ->setLatitude($generator->randomFloat(2, -90, 90))
                ->setLongitude($generator->randomFloat(2, -180, 180))
                ->setCity(AppFixtures::rnd_elem_from_array($cities->all));

            $manager->persist($location);
        }

        $manager->flush();
    }

    private function generateSites(ObjectManager $manager,
                                   Faker\Generator $generator,
                                   int $count = 10)
    {
        for ($i = 0; $i < $count; $i++) {

            $site = new Site;
            $site->setName('ENI ' . $generator->city);

            $manager->persist($site);
        }

        $manager->flush();
    }

    private function generateParticipants(ObjectManager $manager,
                                          Faker\Generator $generator,
                                          int $count = 500)
    {
        $sites = $this->getEntities($manager, Site::class);

        for ($i = 0; $i < $count; $i++) {

            $participant = new Participant;

            $participant
                ->setFirstName($generator->firstName())
                ->setLastName($generator->lastName)
                ->setIsActive($generator->randomFloat(1, 0, 1) > 0.95)
                ->setRoles(['ROLE_USER'])
                ->setMail($generator->email)
                ->setPassword($generator->password)
                ->setPhone($generator->phoneNumber)
                ->setPseudo($generator->userName.rand(0,9999))
                ->setSite(AppFixtures::rnd_elem_from_array($sites->all));

            $manager->persist($participant);
        }
        $manager->flush();
    }

    private function generateOutings(ObjectManager $manager,
                                     Faker\Generator $generator,
                                     int $count = 50)
    {
        $sites        = $this->getEntities($manager, Site::class);
        $locations    = $this->getEntities($manager, Location::class);
        $participants = $this->getEntities($manager, Participant::class);

        for ($i = 0; $i < $count; $i++) {

            $outing = new Outing();

            $dateBegin = $generator->dateTimeBetween('-3 months', '+3months');
            $duration  = rand(3, 240); /*Duration in hours*/
            $dateEventEnd   = \DateTimeImmutable::createFromMutable($dateBegin)->modify("+ {$duration} hours");

            $outing
                ->setName($generator->company)
                ->setDescription($generator->text)
                ->setDateBegin($dateBegin)
                ->setDateEnd($dateEventEnd)
                ->setDuration($duration)
                ->setSite(AppFixtures::rnd_elem_from_array($sites->all))
                ->setLocation(AppFixtures::rnd_elem_from_array($locations->all))
                ->setMaxRegistration(rand(50, 300))
                ->setPlanner(AppFixtures::rnd_elem_from_array($participants->all));


            $outing = $this->setOutingParticipants($manager,$outing);

            $outing = $this->setOutingState($manager,$outing);

            $manager->persist($outing);
        }
        $manager->flush();
    }

    private function setOutingState(ObjectManager $manager, Outing $outing): Outing
    {
        $outingState = new State;
        $states      = $manager->getRepository(State::class)->getStates();
        $now         = new \DateTime('now');

        if($outing->getState()->getLabel() === '')

        switch (true) {
            case $outing->getDateBegin() > $now
                && $outing->getParticipants()->count() < $outing->getMaxRegistration():
                $outingState = $states['Ouverte'];
            break;

            case $outing->getDateBegin() > $now
                && $outing->getParticipants()->count() >= $outing->getMaxRegistration():
                $outingState = $states['Clôturée'];
            break;

            case $outing->getDateBegin() < $now
                && $outing->getDateEnd() > $now:
                $outingState = $states['Activité en cours'];
                break;

            case $outing->getDateEnd() < $now:
                $outingState = $states['Activité passée'];
                break;
        }

        $outingState = (rand(0,100) > 90) ? $states['Activité annulée'] : $outingState;
        $outing->setState($outingState);

        return $outing;
    }

    /**
     * @param Outing $outing
     */
    private function setOutingParticipants(ObjectManager $manager,Outing $outing): Outing
    {
        $participants = $this->getEntities($manager, Participant::class);

        $participantsNumber = rand(1, $outing->getMaxRegistration() + 200);

        $participantsNumber = ($participantsNumber >= $outing->getMaxRegistration())?
            $outing->getMaxRegistration()
            :
            $participantsNumber;

        for($i = 0; $i < $participantsNumber; $i++){

            if($i >= $participants->count - 1) break;

            $randomParticipant = AppFixtures::rnd_elem_from_array($participants->all);

            if($outing->getParticipants()->contains($randomParticipant)) continue;

            $outing->addParticipant($randomParticipant);

        }
        return $outing;
    }

    private function getEntities(ObjectManager $manager, string $className): object
    {
        $entities = $manager->getRepository($className)->findAll();
        /*Le cast en objet permet, une fois crée, de manipuler le tableau
                                        de la même manière qu'un objet (obj->prop)*/
        return (object)[
            "count" => count($entities),
            "all" => $entities,
        ];
    }
    static function rnd_elem_from_array(array $array){
        return $array[rand(0,count($array)-1)];
    }
}