<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\State;
use App\Repository\OutingRepository;
use App\Updators\OutingUpdator;
use App\Verificators\OutingVerificator;
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
                                   EntityManagerInterface $entityManager,
                                   OutingUpdator $updator,
                                   OutingVerificator $verificator): RedirectResponse
    {
        /**
         * @var $user Participant
         */
        $user   = $this->getUser();
        $outing = $outingRepository->find($id);

        if($verificator->canAdd($user,$outing)){

            $outing  = $outing->addParticipant($user);
            $outing  = $updator->updateState($outing);

            $entityManager->persist($outing);
            $entityManager->flush();
        }
        else{
            $this->addFlash('Opération impossible',$verificator->getErrorMessages());
        }
        return $this->redirectToRoute('outing');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("outing/removeParticipant/{id}", name="outing_removefrom", requirements={"page"="\d+"})
     */
    public function removeParticipant(int $id,
                                   OutingRepository  $outingRepository,
                                   EntityManagerInterface $entityManager,
                                   OutingUpdator $updator,
                                   OutingVerificator $verificator): RedirectResponse
    {
        /**
         * @var $user Participant
         */
        $user = $this->getUser();
        $outing = $outingRepository->find($id);

        if($verificator->canRemove($user,$outing)){

            $outing  = $outing->removeParticipant($user);
            $outing  = $updator->updateState($outing);

            $entityManager->persist($outing);
            $entityManager->flush();
        }
        else{
            $this->addFlash('Opération impossible',$verificator->getErrorMessages());
        }

        return $this->redirectToRoute('outing');
    }

}
