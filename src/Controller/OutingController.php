<?php

namespace App\Controller;

use App\Repository\OutingRepository;
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

       $user= $this->getUser();

        return $this->render('outing/list.html.twig', ["outings"=>$outings, "currentPage"=> $page, "maxPage"=>$maxPage, "user"=> $user

        ]);
    }
}
