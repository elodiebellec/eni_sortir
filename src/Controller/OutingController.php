<?php

namespace App\Controller;

use App\Form\FilterType;
use App\Model\OutingsFilter;
use App\Repository\OutingRepository;
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

    public function  list (int $page=1,  Request $request,OutingRepository  $outingRepository): Response
    {
        $user= $this->getUser();


        $filter = new OutingsFilter();
        $filterForm= $this->createForm(FilterType::class, $filter);
        $filterForm->handleRequest($request);

        $results = $outingRepository->findAllOutings($page, $filter, $user);
        //$outingsQuantity = $outingRepository->count([]);
       // $maxPage= ceil($outingsQuantity/10);
        $maxPage=ceil($results['maxOutings']/10);
    dump($maxPage);

       /*if($filterForm->isSubmitted()&& $filterForm->isValid())
       {

           $outings = $outingRepository->findAllOutings($page, $filter, $user);

       } */


        return $this->render('outing/list.html.twig',
            ["outings"=>$results['outings'],
            "maxOutings"=>$results['maxOutings'],
            "currentPage"=> $page,
            "maxPage"=>$maxPage,
            "user"=> $user,
            "formulaire"=>$filterForm->createView()

        ]);
    }
}
