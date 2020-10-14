<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil/{id}", name="profil")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getProfile(int $id)
    {
        // Récupération de l'utilisateur
        $user = $this->findUser($id);

        // L'utilisateur n'éxiste pas
        if(!$user) $this->notFound();

        return $this->render('profil/profil.html.twig', [
            'controller_name' => 'ProfilController',
            'user' => $user,
            'title' => 'Profile de '.$user->getPseudo()
        ]);
    }

    /**
     * @Route("/profil/edit/{id}", name="edit-profil")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editProfile(int $id, Request $request){

        // Récupération du profil
        $user = $this->findUser($id); //$this->getUser();
        dump($user);

        // L'utilisateur n'éxiste pas
        if(!$user) $this->notFound();

        // Création du formulaire
        $form = $this->createForm(ParticipantType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $avatarFile = $form->get('urlPhoto')->getData();
            $user = $form->getData();
            dump($user);
            if($avatarFile){
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                $user->setUrlPhoto(new File($this->getParameter('avatars_directory').'/'.$user->getBrochureFilename()));

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->flush($user);
                $user = $manager->find($user->getId());

                return $this->redirectToRoute('edit-profil', [
                    'user' => $user
                ]);
            }
        }

        return $this->render('profil/profil-edit.html.twig', [
            'controller_name' => 'ProfilController',
            'form' => $form->createView(),
            'title' => 'Edition de mon profil'
        ]);
    }

    private function findUser(int $id){
        // Récupération du profil utilisateur
        $em = $this->getDoctrine()->getManager();
        return $em->find(Participant::class, $id);
    }

    private function notFound(){
        return $this->render('exception/error404.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
}
