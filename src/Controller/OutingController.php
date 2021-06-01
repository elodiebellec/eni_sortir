<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\OutingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                                   EntityManagerInterface $entityManager){

        $outing = $outingRepository->find($id);

        /**
         * @var $user Participant
         */
        $user = $this->getUser();

        if($user->getOutings()->contains($outing)){
            $this->addFlash('operation denied','Vous êtes déjà inscrit à cet évènement.');
            return $this->redirectToRoute('outing');
        }
        if(strcmp($outing->getPlanner()->getPseudo(), $user->getPseudo()) === 0){
            $this->addFlash('operation denied',"Vous organisez cet évènement.");
            return $this->redirectToRoute('outing');
        }

        $entityManager->persist($outing->addParticipant($user));
        $entityManager->flush();

        return $this->redirectToRoute('outing');
    }
}
