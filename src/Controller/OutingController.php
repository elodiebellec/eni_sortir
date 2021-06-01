<?php

namespace App\Controller;

use App\Repository\OutingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    /**
     * @Route("/outing", name="outing")
     */
    public function index(): Response
    {
        return $this->render('outing/index.html.twig', [
            'controller_name' => 'OutingController',
        ]);
    }

    public function  list (OutingRepository  $outingRepository): Response
    {


        $outings =  $outingRepository->findAll();

        return $this->render('outing/list.html.twig', ["outings"=>$outings

        ]);
    }
}
