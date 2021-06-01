<?php


namespace App\Controller;


use App\Form\ParticipantType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class ParticipantController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/update", name="participant_profile_update")
     */
    public function update(Request $request)
    {
        $user = $this->getUser();
        $updateForm = $this->createForm(ParticipantType::class,$user);
        $updateForm->handleRequest($request);

        if($updateForm->isSubmitted() && $updateForm->isValid()){
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success','Profile modifié ! ');
            $this->redirectToRoute('main_home');
        }

        return $this->render('participant/updateProfile.html.twig', [
            'updateForm' => $updateForm->createView()
        ]);
    }
}