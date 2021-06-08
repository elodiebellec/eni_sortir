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

        //Display user School Site
        $userSite = $this->getUser()->getSite();
        $outing->setSite($userSite);

        //Set the form state to create ("créée") when the form is submited
        $outing->setState($entityManager->getRepository(State::class)->getState('Créée'));
        $outingForm = $this->createForm(OutingType::class, $outing);


        $outingForm->handleRequest($request);

            if($outingForm->isSubmitted() && $outingForm->isValid()){
                //If the button 'saveAndAdd' ('publier la sortie') is cliked, set the form state to open ("ouverte")
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
    //function to get the location when a city is selected in the creation outing form
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
        $data = [];
        $data['postalCode'] = $city->getPostalCode();
        $data['locations'] = [];
        foreach($city->getLocations() as $location){
            $data['locations'][$location->getName()] = [];
            $data['locations'][$location->getName()]['name'] = $location->getName();
            $data['locations'][$location->getName()]['street'] = $location->getStreet();
            $data['locations'][$location->getName()]['latitude'] = $location->getLatitude();
            $data['locations'][$location->getName()]['longitude'] = $location->getLongitude();
        }
        return new JsonResponse($data);
    }


    

    /**
     * @Route("outing/cancel/{id}", name="outing_cancel", requirements={"page"="\d+"})
     */
    public function cancel($id, outingRepository $outingRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $outing = $outingRepository->find($id);
        if(!$outing) {
            throw $this->createNotFoundException("Cette sortie n'existe plus !");
        }

        /**
         * @var $user Participant
         *
         */
        $outing->setPlanner($this->getUser());

        //Display user School Site
        $userSite = $this->getUser()->getSite();
        $outing->setSite($userSite);

        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if($outingForm->isSubmitted() && $outingForm->isValid()){

                $outing->setState($entityManager->getRepository(State::class)->getState('Activité annulée'));


            $entityManager->persist($outing);
            $entityManager->flush();

            //TODO flash must display on outing page
            $this->addFlash('success', 'Sortie annulée !');
            return $this->redirectToRoute('outing');
        }

        return $this->render('outing/cancel.html.twig', [
            'outing' => $outing,
            'outingForm'=> $outingForm->createView(),
            'userSite'=> $userSite

        ]);

    }

    /**
     * @Route("outing/update/{id}", name="outing_update", requirements={"page"="\d+"})
     */
    public function update($id, outingRepository $outingRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $outing = $outingRepository->find($id);
        if(!$outing) {
            throw $this->createNotFoundException("Cette sortie n'existe plus !");
        }

        /**
         * @var $user Participant
         *
         */
        $outing->setPlanner($this->getUser());

        //Display user School Site
        $userSite = $this->getUser()->getSite();
        $outing->setSite($userSite);

        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if($outingForm->isSubmitted() && $outingForm->isValid()){

            $entityManager->persist($outing);
            $entityManager->flush();

            //TODO flash must display on outing page
            $this->addFlash('success', 'Sortie annulée !');
            return $this->redirectToRoute('outing');
        }

        return $this->render('outing/update.html.twig', [
            'outing' => $outing,
            'outingForm'=> $outingForm->createView(),
            'userSite'=> $userSite

        ]);

    }




}
