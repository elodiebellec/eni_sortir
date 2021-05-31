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

        $this->generateCities($manager, $generator);

        $this->generateLocation($manager, $generator);

        $this->generateSites($manager, $generator);

        $this->generateParticipants($manager, $generator);

        $this->generateOutings($manager,$generator);
    }

    private function defineStates(ObjectManager $manager): void
    {
        $created    = new State;
        $opened     = new State;
        $fenced     = new State;
        $inProgress = new State;
        $finished   = new State;
        $canceled   = new State;

        $created    ->setLabel("Créée");
        $opened     ->setLabel("Ouverte");
        $fenced     ->setLabel("Clôturée");
        $inProgress ->setLabel("Activité en cours");
        $finished   ->setLabel("Activité passée");
        $canceled   ->setLabel("Activité annulée");

        $manager    ->persist($created);
        $manager    ->persist($fenced);
        $manager    ->persist($inProgress);
        $manager    ->persist($finished);
        $manager    ->persist($canceled);

        $manager    ->flush();
    }

    private function generateCities(ObjectManager $manager,
                                    Faker\Generator $generator,
                                    int $count = 30): void
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
                                      int $count = 50): void
    {
        $cities = $this->getEntities($manager,City::class);

        for ($i = 0; $i < $count; $i++) {

            $location = new Location;

            $location
                ->setName($generator->company)
                ->setStreet($generator->streetAddress)
                ->setLatitude($generator->randomFloat(2, -90, 90))
                ->setLongitude($generator->randomFloat(2, -180, 180))
                ->setCity(random_element($cities->all))
            ;

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
            $site->setName('ENI '.$generator->city);

            $manager->persist($site);
        }

        $manager->flush();
    }

    private function generateParticipants(ObjectManager $manager,
                                          Faker\Generator $generator,
                                          int $count = 100)
    {
        $sites = $this->getEntities($manager,Site::class);

        for ($i = 0; $i < $count; $i++) {

            $participant = new Participant;

            $participant
                ->setFirstName($generator->firstName())
                ->setLastName($generator->lastName)
                ->setIsActive($generator->randomFloat(1,0,1) > 0.95)
                ->setRoles(['ROLE_USER'])
                ->setMail($generator->email)
                ->setPassword($generator->password)
                ->setPhone($generator->phoneNumber)
                ->setPseudo($generator->userName)
                ->setSite(random_element($sites->all))
            ;

            $manager->persist($participant);
        }
        $manager->flush();
    }

    private function generateOutings(ObjectManager $manager,
                                     Faker\Generator $generator,
                                     int $count = 15)
    {
        $sites        = $this->getEntities($manager,Site::class);
        $locations    = $this->getEntities($manager,Location::class);
        $participants = $this->getEntities($manager,Participant::class);
        $states       = $this->getEntities($manager,State::class);

        for ($i = 0; $i < $count; $i++) {

            $outing     = new Outing();

            $dateBegin  = $generator->dateTimeBetween('-6 months','+6months');

            $dateEnd    = \DateTimeImmutable::createFromMutable($dateBegin)->modify('+2days');

            $dateEnd    = $dateEnd->modify('+1day');

            $outing
                ->setName($generator->company)
                ->setDescription($generator->text)
                ->setDateBegin($dateBegin)
                ->setDateEnd($dateEnd)
                ->setDuration(random_int(120,500))
                ->setSite(random_element($sites->all))
                ->setLocation(random_element($locations->all))
                ->setMaxRegistration(random_int(50,300))
                ->setPlanner(random_element($participants->all))
                ->setState($states->all[0])
            ;

            $manager->persist($outing);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @return array
     */
    private function getEntities(ObjectManager $manager, string $className): object
    {
        $entities = $manager->getRepository($className)->findAll();
        /*Le cast en objet permet de manipuler le résultat de la même manière qu'un objet*/
        return (object)[
            "count"=> count($entities),
            "all"=>$entities,
        ];
    }



}
