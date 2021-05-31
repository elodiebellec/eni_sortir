<?php

namespace App\DataFixtures;

use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $generator = Faker\Factory::create('fr_FR');

        $this->registerStates($manager);

        for($i=0;$i<20;$i++){

        }

        $manager->flush();
    }

    private function registerStates(ObjectManager $manager){
        /*DEFINE STATES*/
        $created = new State;
        $created->setLabel("Créée");

        $opened = new State;
        $opened->setLabel("Ouverte");

        $fenced = new State;
        $fenced->setLabel("Clôturée");

        $inProgress = new State;
        $inProgress->setLabel("Activité en cours");

        $finished  = new State;
        $finished->setLabel("Activité passée");

        $canceled  = new State;
        $canceled ->setLabel("Activité annulée");

        /*PERSIST STATES*/
        $manager->persist($created);
        $manager->persist($fenced);
        $manager->persist($inProgress);
        $manager->persist($finished);
        $manager->persist($canceled);
    }
}
