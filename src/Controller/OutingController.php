<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Entity\Participant;
use App\Entity\State;
use App\Repository\OutingRepository;
use App\Repository\StateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{page}", name="outing", requirements= {"page"="\d+"})
     */

    public function  list (int $page=1, OutingRepository  $outingRepository): Response
    {

        $outings =  $outingRepository->findAllOutings($page);
        dump($outings);
        $outingsQuantity = $outingRepository->count([]);
        $maxPage= ceil($outingsQuantity/10);

        return $this->render('outing/list.html.twig', ["outings"=>$outings, "currentPage"=> $page, "maxPage"=>$maxPage

        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("outing/addParticipant/{id}", name="outing_registerto", requirements={"page"="\d+"})
     */
    public function addParticipant(int $id,
                                   OutingRepository  $outingRepository,
                                   EntityManagerInterface $entityManager): RedirectResponse
    {
        $outing = $outingRepository->find($id);

        /**
         * @var $user Participant
         */
        $user = $this->getUser();

        if($user->getOutings()->contains($outing)){
            $this->addFlash('Opération impossible','Vous êtes déjà inscrit à cet évènement.');
            return $this->redirectToRoute('outing');
        }
        if($outing->getParticipants()->count() >= $outing->getMaxRegistration()){
            $this->addFlash('Opération impossible','Cet évènement ne comporte plus de places disponibles.');
            return $this->redirectToRoute('outing');
        }
        if(strcmp($outing->getPlanner()->getPseudo(), $user->getPseudo()) === 0){
            $this->addFlash('Opération impossible',"Vous organisez cet évènement, de fait vous y participez.");
            return $this->redirectToRoute('outing');
        }

        $outing = $outing->addParticipant($user);
        $outing = $this->updateState($outing);

        $entityManager->persist($outing);
        $entityManager->flush();

        return $this->redirectToRoute('outing');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("outing/removeParticipant/{id}", name="outing_removefrom", requirements={"page"="\d+"})
     */
    public function removeParticipant(int $id,
                                   OutingRepository  $outingRepository,
                                   EntityManagerInterface $entityManager): RedirectResponse
    {

        $outing = $outingRepository->find($id);

        /**
         * @var $user Participant
         */
        $user = $this->getUser();

        if(!$user->getOutings()->contains($outing)){
            $this->addFlash('Opération impossible','Vous n\'êtes pas inscrit à cet évènement.');
            return $this->redirectToRoute('outing');
        }
        if(strcmp($outing->getPlanner()->getPseudo(), $user->getPseudo()) === 0){
            $this->addFlash('Opération impossible',"Vous organisez cet évènement, vous ne pouvez pas vous désister.");
            return $this->redirectToRoute('outing');
        }
        if($outing->getState()->getLabel() === 'Activité passée'
        || $outing->getState()->getLabel() === 'Activité annulée'){
            $this->addFlash('Opération impossible',"Impossible de se désister d'une activité non existante.");
            return $this->redirectToRoute('outing');
        }

        $outing = $outing->removeParticipant($user);
        $outing = $this->updateState($outing);

        $entityManager->persist($outing);
        $entityManager->flush();

        return $this->redirectToRoute('outing');
    }

    private function updateState(Outing $outing): Outing
    {
        $states = $this->getDoctrine()->getRepository(State::class)->getStates();

        if($outing->getState()->getLabel() === 'Activité annulée') return $outing;

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
