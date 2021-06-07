<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\FilterType;
use App\Model\OutingsFilter;
use App\Entity\Outing;
use App\Entity\Participant;
use App\Entity\State;
use App\Form\OutingType;
use App\Repository\CityRepository;
use App\Repository\OutingRepository;
use App\Updators\OutingUpdator;
use App\Verificators\OutingVerificator;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Array_;
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

    public function  list ( Request $request,
                            OutingRepository  $outingRepository): Response
    {

        $user= $this->getUser();
        $filter = new OutingsFilter();
        $filterForm= $this->createForm(FilterType::class, $filter);
        $filterForm->handleRequest($request);

        $page =(int) $request->request->get('pageButtton', 1);
        $countOutingsFromBDD= $outingRepository->count([]);
        $maxPagesForAllResearch = $countOutingsFromBDD/10;

        if ($page >= 1 && $page <= $maxPagesForAllResearch) {
            $results = $outingRepository->findAllOutings($page, $filter, $user);
        } else {
            throw $this->createNotFoundException("Oops ! 404 ! This page does not exist !");
        }

        $outingsQuantity =  sizeof($results['outings']);
        $maxPage= ceil($outingsQuantity); // orig val : /10

        return $this->render('outing/list.html.twig',
            ["outings"=>$results['outings'],
            "maxOutings"=>$outingsQuantity,
            "currentPage"=> $page,
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
        //dd($outing);
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
     * @Route("outing/create", name="outing_create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager,CityRepository $cityRepository): Response
    {

        $outing = new Outing();
        /**
         * @var $user Participant
         */

        //dd($cityRepository->findCityByNameWithLocations("Lenoir"))[0];
        $outing->setPlanner($this->getUser());

        //Display user Site
        $userSite = $this->getUser()->getSite();
        $outing->setSite($userSite);






        $outing->setState($entityManager->getRepository(State::class)->getState('Créée'));
        $outingForm = $this->createForm(OutingType::class, $outing);


        $outingForm->handleRequest($request);

            if($outingForm->isSubmitted() && $outingForm->isValid()){

                    if ($outingForm->getClickedButton() && 'saveAndAdd' === $outingForm->getClickedButton()->getName()) {
                        $outing->setState($entityManager->getRepository(State::class)->getState('Ouverte'));
                    }

                    $entityManager->persist($outing);
                    $entityManager->flush();

                    //TODO flash must display on outing page
                    $this->addFlash('success', 'Sortie ajoutée !');
                    return $this->redirectToRoute('outing');
                }
        return $this->render('outing/create.html.twig', [
            'outingForm'=> $outingForm->createView(),
            'userSite'=> $userSite
        ]);

    }
    /**
     * @Route("outing/ajax-cityData", name="outing_ajax_city")
     */
    public function getCityData(Request $request,
                                CityRepository $cityRepository): JsonResponse
    {

        $selectedCity = json_decode($request->getContent());
        /**
         * @var City $city
         */
        $city = $cityRepository->findCityByNameWithLocations($selectedCity->cityName)[0];
        $locations = [];
        foreach($city->getLocations() as $location){
            $locations[$location->getName()] = $location->getName();
        }
        return new JsonResponse($locations);
    }

}
