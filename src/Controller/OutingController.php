<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Entity\Participant;
use App\Entity\State;
use App\Form\OutingType;
use App\Repository\OutingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/create", name="outing_create")
     */

    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        $outing = new Outing();
        /**
         * @var $user Participant
         */
        $outing->setPlanner($this->getUser());
        $userSite = $this->getUser()->getSite();
        $outing->setSite($userSite);
        $cityList = $entityManager->getRepository('App:State')->findAll();

        //TODO switch method find(by id) with method findByLabel
        $outing->setState($entityManager->getRepository('App:State')->find(1));
        $outingForm = $this->createForm(OutingType::class, $outing);


        $outingForm->handleRequest($request);

        if($outingForm->isSubmitted() && $outingForm->isValid()){
            if ($outingForm->getClickedButton() && 'saveAndAdd' === $outingForm->getClickedButton()->getName()) {
                $outing->setState($entityManager->getRepository('App:State')->find(2));
            }

            $entityManager->persist($outing);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée !');
            return $this->redirectToRoute('outing_create');

        }



        return $this->render('outing/create.html.twig', [
            'outingForm'=> $outingForm->createView(),
            'userSite'=> $userSite
        ]);

    }
}
