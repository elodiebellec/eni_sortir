<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\FilterType;
use App\Form\OutingCancellationType;
use App\Model\OutingsFilter;
use App\Entity\Outing;
use App\Entity\Participant;
use App\Entity\State;
use App\Form\OutingType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\OutingRepository;
use App\Updators\OutingUpdator;
use App\Verificators\OutingVerificator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/outing", name="outing")
     */

public function  list ( Request $request,
                        OutingRepository  $outingRepository,
                        OutingUpdator $updator,
                        EntityManagerInterface $entityManager): Response
    {

        $user= $this->getUser();
        $filter = new OutingsFilter();
        $filterForm= $this->createForm(FilterType::class, $filter);
        $filterForm->handleRequest($request);

        $page =(int) $request->request->get('pageButtton', 1);
        $updateOutingStatus = $outingRepository->findAllForStateUpdate();

        $countOutingsFromBDD= sizeof($updateOutingStatus);

        $maxPagesForAllResearch = ceil($countOutingsFromBDD/10);

        foreach ($updateOutingStatus as $value)
        {
            if($value->getPlanner()->getPseudo() == $user->getUsername() && ! ($value->getParticipants()->contains($user) ) )
            {
                $value->addParticipant($user);

            }

            $updatedOuting= $updator->updateState($value);
            $entityManager->persist($updatedOuting);

        }

        $entityManager->flush();


        /**
         * @var Outing $outing
         */


        if ($page >= 1 && $page <= $maxPagesForAllResearch) {
            $results = $outingRepository->findAllOutings($page, $filter, $user);

        } else {
            throw $this->createNotFoundException("Oops ! 404 ! This page does not exist !");
        }


        $outingsQuantity =  sizeof($results);
        $maxPage= ceil($outingsQuantity/10);
        if($maxPage<1)
        {
            $maxPage=1;
        }


        return $this->render('outing/list.html.twig',
            ["outings"=> $results,
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
                                            OutingRepository $outingRepository,
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
        if (!$outing) return $this->redirectToRoute('outing');
        /**
         *  Update in the case the state has changed since the page has been loaded.
         */
        $outing = $updator->updateState($outing);

        if ($verificator->canAdd($user, $outing)) {
            $outing = $outing->addParticipant($user);
            $outing = $updator->updateState($outing);

        } else {
            $this->addFlash('Opération impossible', $verificator->getErrorMessages());
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
                                               OutingRepository $outingRepository,
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
        if (!$outing) return $this->redirectToRoute('outing');
        /**
         *  Update in the case the state has changed since the page has been loaded.
         */
        $outing = $updator->updateState($outing);

        if ($verificator->canRemove($user, $outing)) {
            $outing = $outing->removeParticipant($user);
            $outing = $updator->updateState($outing);
        } else {
            $this->addFlash('Opération impossible', $verificator->getErrorMessages());
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
    public function create(Request $request,
                           EntityManagerInterface $entityManager,
                           LocationRepository $locationRepository): Response
    {

        $outing = new Outing();

        /**
         * @var $user Participant
         */
        $user = $this->getUser();
        $outing->setPlanner($user);
        /**
         * systematicaly adds the planner into participant list
         */
        $outing->addParticipant($user);


        //Display user School Site
        $userSite = $user->getSite();
        $outing->setSite($userSite);

        //Set the form state to create ("créée") when the form is submited
        $outing->setState($entityManager->getRepository(State::class)->getState('Créée'));
        $outingForm = $this->createForm(OutingType::class, $outing);

        $outingForm->handleRequest($request);



        if ($outingForm->isSubmitted() && $outingForm->isValid()) {

            //If the button 'saveAndAdd' ('publier la sortie') is cliked, set the form state to open ("ouverte")

            if ($outingForm->getClickedButton() && 'saveAndAdd' === $outingForm->getClickedButton()->getName()) {
                $outing->setState($entityManager->getRepository(State::class)->getState('Ouverte'));
            }

            // liste des partcipants

            $entityManager->persist($outing);
            $entityManager->flush();

            //flash message displaying on outing page
            $this->addFlash('success', 'Sortie ajoutée !');
            return $this->redirectToRoute('outing');
        }
        return $this->render('outing/create.html.twig', [
            'outingForm' => $outingForm->createView(),
            'userSite' => $userSite
        ]);

    }
    //function to get the location when a city is selected in the creation outing form

    /**
     * @Route("outing/ajax-cityData", name="outing_ajax_creation")
     * @Route("outing/update/ajax-cityData", name="outing_ajax_update")
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
        foreach ($city->getLocations() as $location) {
            $data['locations'][$location->getName()] = [];
            $data['locations'][$location->getName()]['name'] = $location->getName();
            $data['locations'][$location->getName()]['street'] = $location->getStreet();
            $data['locations'][$location->getName()]['latitude'] = $location->getLatitude();
            $data['locations'][$location->getName()]['longitude'] = $location->getLongitude();
            $data['locations'][$location->getName()]['id'] = $location->getId();
        }
        return new JsonResponse($data);
    }


    

    /**
     * @Route("outing/cancel/{id}", name="outing_cancel", requirements={"page"="\d+"})
     */
    public function cancel($id, outingRepository $outingRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $outing = $outingRepository->find($id);
        if (!$outing) {
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

        //Display outing location
        $outingLocation = $outing->getLocation()->getName();

        $outingCancellationForm = $this->createForm(OutingCancellationType::class, $outing);
        $outingCancellationForm->handleRequest($request);

        if($outingCancellationForm->isSubmitted() && $outingCancellationForm->isValid()){
            $outing->setState($entityManager->getRepository(State::class)->getState('Activité annulée'));
            $entityManager->persist($outing);
            $entityManager->flush();

            //flash message displaying on outing page
            $this->addFlash('success', 'Sortie annulée !');
            return $this->redirectToRoute('outing');
        }

        return $this->render('outing/cancel.html.twig', [
            'outing' => $outing,
            'outingLocation' => $outingLocation,
            'outingCancellationForm'=> $outingCancellationForm->createView(),
            'userSite'=> $userSite

        ]);
    }

    /**
     * @Route("outing/update/{id}", name="outing_update", requirements={"page"="\d+"})
     */
    public function update($id=1, outingRepository $outingRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $outing = $outingRepository->find($id);
        if(!$outing) {
            throw $this->createNotFoundException("Cette sortie n'existe plus !");
        }

        //Display user School Site
        $userSite = $this->getUser()->getSite();
        $outing->setSite($userSite);

        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if($outingForm->isSubmitted() && $outingForm->isValid()){
            //If the button 'saveAndAdd' ('publier la sortie') is cliked, set the form state to open ("ouverte")

            if ($outingForm->getClickedButton() && 'saveAndAdd' === $outingForm->getClickedButton()->getName()) {
                $outing->setState($entityManager->getRepository(State::class)->getState('Ouverte'));
            }

            $entityManager->persist($outing);
            $entityManager->flush();

            //flash message displaying on outing page
            $this->addFlash('success', 'Sortie modifiée !');
            return $this->redirectToRoute('outing');
        }

        return $this->render('outing/update.html.twig', [
            'outing' => $outing,
            'outingForm'=> $outingForm->createView(),
            'userSite'=> $userSite

        ]);

    }

    /**
     * @Route("/outing/delete/{id}", name="outing_delete")
     */
    public function delete($id,
                           EntityManagerInterface $entityManager): Response
    {

        $outing = $entityManager->find(outing::class, $id);
        $entityManager->remove($outing);
        $entityManager->flush();

        //flash message displaying on outing page
        $this->addFlash('sucess', 'Sortie supprimée !!');

        return $this->redirectToRoute('outing');
    }

    /**
     * @Route("/outing/show", name="outing_show")
     */
    public function show(Request $request, OutingRepository $repository): Response
    {

        $OutingId =(int) $request->query->get('id', 1);

        $outing = $repository->find($OutingId);
        //dd($outing->getParticipants()->count());

        return $this->render('outing/showOneOuting.html.twig', [
            'outing' => $outing
        ]);
    }

    /**
     * @Route("/outing/publish", name="outing_publish")
     */
    public function publish(Request $request, OutingRepository $repository,  EntityManagerInterface $entityManager): Response
    {

        $OutingId =(int) $request->query->get('id', 1);
        $outing = $repository->find($OutingId);

        $outing->setState($entityManager->getRepository(State::class)->getState('Ouverte'));

        $entityManager->persist($outing);
        $entityManager->flush();

        $this->addFlash('success', 'Sortie publiée !');

        return $this->redirectToRoute('outing');


    }





}
