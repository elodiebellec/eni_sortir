<?php

namespace App\Controller;

use App\Form\FilterType;
use App\Model\OutingsFilter;

use App\Entity\Outing;
use App\Entity\Participant;
use App\Entity\State;
use App\Form\OutingType;
use App\Repository\OutingRepository;
use App\Updators\OutingUpdator;
use App\Verificators\OutingVerificator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/outing", name="outing")
     */

    public function  list ( Request $request,OutingRepository  $outingRepository): Response
    {

        $user= $this->getUser();

        $page = (int)$request->query->get("page", 1);
        $ajax = $request->get("ajax",1);
        $filter = new OutingsFilter();
        $filterForm= $this->createForm(FilterType::class, $filter);
        $filterForm->handleRequest($request);

        $testPage =(int) $request->request->get('pageButtton', 1);

        if ($testPage >= 1 ) {
            $results = $outingRepository->findAllOutings($testPage, $filter, $user);
        } else {
            throw $this->createNotFoundException("Oops ! 404 ! This page does not exist !");
        }




        //$outingsQuantity = $outingRepository->count([]);
       // $maxPage= ceil($outingsQuantity/10);
        $maxPage=ceil($results['maxOutings']/10);

        dump($filter);
        dump($filterForm);
        dump($testPage);


        return $this->render('outing/list.html.twig',
            ["outings"=>$results['outings'],
            "maxOutings"=>$results['maxOutings'],
            "currentPage"=> $testPage,
            "maxPage"=>$maxPage,
            "user"=> $user,
            "formulaire"=>$filterForm->createView()

        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("outing/addParticipant/{id}", name="outing_addParticipant", requirements={"page"="\d+"})
     */
    public function addParticipantWithCheck(int $id,
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
        /**
         * Check for nulls.
         */
        if(!$outing) return $this->redirectToRoute('outing');
        /**
         *  Update in the case the state has changed since the page has been loaded.
         */
        $outing  = $updator->updateState($outing);

        if($verificator->canAdd($user,$outing)){
            $outing  = $outing->addParticipant($user);
            $outing  = $updator->updateState($outing);
        }
        else{
            $this->addFlash('Opération impossible',$verificator->getErrorMessages());
        }
        /**
         * Refresh the outing and its state even if participant couldn't be added.
         * It will refresh outing for all users.
         */
        $entityManager->persist($outing);
        $entityManager->flush();

        return $this->redirectToRoute('outing');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("outing/removeParticipant/{id}", name="outing_removeParticipant", requirements={"page"="\d+"})
     */
    public function removeParticipantWithCheck(int $id,
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

        /**
         * Check for nulls.
         */
        if(!$outing) return $this->redirectToRoute('outing');
        /**
         *  Update in the case the state has changed since the page has been loaded.
         */
        $outing  = $updator->updateState($outing);

        if($verificator->canRemove($user,$outing)){
            $outing  = $outing->removeParticipant($user);
            $outing  = $updator->updateState($outing);
        }
        else{
            $this->addFlash('Opération impossible',$verificator->getErrorMessages());
        }
        /**
         * Refresh the outing and its state even if participant couldn't be added.
         * It will refresh outing for all users.
         */
        $entityManager->persist($outing);
        $entityManager->flush();

        return $this->redirectToRoute('outing');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/create", name="outing_create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        //TODO Générer un formulaire pour ajouter un nouveau souhait
        $outing = new Outing();
        /**
         * @var $user Participant
         */
        $outing->setPlanner($this->getUser());
        //$userSite->
        $outing->setSite($this->getUser()->getSite());


        $outing->setState($entityManager->getRepository(State::class)->getState('Créée'));
        $outingForm = $this->createForm(OutingType::class, $outing);


        $outingForm->handleRequest($request);

        if($outingForm->isSubmitted() && $outingForm->isValid()){

            $entityManager->persist($outing);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée !');
            return $this->redirectToRoute('outing');
        }
        return $this->render('outing/create.html.twig', [
            'outingForm'=> $outingForm->createView()
        ]);

    }

}
