<?php


namespace App\Controller;


use App\Entity\Participant;
use App\FileUploader\FileUploader;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
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
    public function update(Request $request,FileUploader $uploader)
    {
        /**
         * @var Participant $user
         */
        $user = $this->getUser();
        $updateForm = $this->createForm(ParticipantType::class,$user);
        $updateForm->handleRequest($request);

        if($updateForm->isSubmitted() && $updateForm->isValid()){

            if($photo = $updateForm->get('photo')->getData()){
                $uploader->saveImage($photo);
                $user->setPhoto($uploader->getLastUploadedFile());
            }

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success','Profile modifié ! ');
            return $this->redirectToRoute('outing');
        }

        return $this->render('participant/updateProfile.html.twig', [
            'updateForm' => $updateForm->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile/show", name="participant_profile_show")
     */
    public function show(Request $request, ParticipantRepository $repository)
    {


        $participantId =(int) $request->query->get('id', 1);
        $participant = $repository->find($participantId);

        return $this->render('participant/showProfile.html.twig', [
            'participant' => $participant
        ]);
    }

}