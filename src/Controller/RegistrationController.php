<?php

namespace App\Controller;

use App\Entity\Participant;
use App\FileUploader\FileUploader;
use App\Form\CsvType;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use App\Serializer\CsvSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     *
     * @Route("admin/register", name="app_register")
     */
    public function register(Request $request,
                             UserPasswordEncoderInterface $passwordEncoder,
                             GuardAuthenticatorHandler $guardHandler): Response
    {

        $newParticipant = new Participant();
        $newParticipant->setRoles(["ROLE_USER"]);

        $form = $this->createForm(RegistrationFormType::class, $newParticipant);
        $form->handleRequest($request);

        $csvForm = $this->createForm(CsvType::class,null,[
            'action' => $this->generateUrl('admin_register_csv'),
            'method' => 'POST',
                ]
        );
        $csvForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newParticipant->setPassword(
                $passwordEncoder->encodePassword(
                    $newParticipant,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newParticipant);
            $entityManager->flush();

            $this->addFlash("Succes", "Vous avez bien inscrit ce participant !");

            return $this->redirectToRoute('outing');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'csvForm' => $csvForm->createView()
        ]);
    }

    /**
     * Register users with CSV files
     * @IsGranted("ROLE_USER")
     * @Route("/admin/csvregister", name="admin_register_csv")
     */
    public function registerToCSV(Request $request,
                                  FileUploader $uploader,
                                  CsvSerializer $serializer): RedirectResponse
    {
        $fileSystem = new Filesystem;
        $hasUploaded = $uploader->saveCSV($request->files->get("csv")['file']);

        if(!$hasUploaded) {
            $this->addFlash("Failure", "Fichier incorrect");
            return $this->redirectToRoute('app_register');
        }

        $participantsHaveBeenCreated = $serializer->convertToUsers($uploader->getLastUploadedFile());

        if(!$participantsHaveBeenCreated){
            $this->addFlash("Failure", "Damned ! Nous n'avons pas pu inscrire ces participants, les données doivent être défectueuses");

            return $this->redirectToRoute('outing');
        }

        $fileSystem->remove($uploader->getLastUploadedFile());

        $this->addFlash("Succes", "Vous avez bien inscrit ces participants !");

        return $this->redirectToRoute('outing');
    }
}
