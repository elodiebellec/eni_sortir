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

        $name = $filter->getName();
        $dateBegin= $filter->getDateBegin();
        $dateEnd = $filter->getDateEnd();
        $site = $filter ->getSite();
        $isPlanner= $filter->getIsPlanner();
        $isRegistered= $filter->getIsRegistered();
        $isNotRegistered = $filter->getIsNotRegistered();
        $isOutDated= $filter->getIsOutDated();

       // $outings = $outingRepository->findAllOutings($page, $name, $dateBegin, $dateEnd, $site, $isPlanner, $isRegistered, $isNotRegistered, $isOutDated, $user);
        $outings= $outingRepository->findAll();
        $outingsQuantity = $outingRepository->count([]);
        $maxPage= ceil($outingsQuantity/10);



       if($filterForm->isSubmitted()&& $filterForm->isValid())
       {
           // appel methode repository

           $name = $filter->getName();
           $dateBegin= $filter->getDateBegin();
           $dateEnd = $filter->getDateEnd();
           $site = $filter ->getSite();
           $isPlanner= $filter->getIsPlanner();
           $isRegistered= $filter->getIsRegistered();
           $isNotRegistered = $filter->getIsNotRegistered();
           $isOutDated= $filter->getIsOutDated();

           dump($filterForm);

          // $outings = $outingRepository->findAllOutings($page, $name, $dateBegin, $dateEnd, $site, $isPlanner, $isRegistered, $isNotRegistered, $isOutDated, $user);


       }




        return $this->render('outing/list.html.twig', ["outings"=>$outings, "currentPage"=> $page, "maxPage"=>$maxPage, "user"=> $user, "formulaire"=>$filterForm->createView()

        ]);
    }
}
