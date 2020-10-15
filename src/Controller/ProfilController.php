<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function editProfile(int $id, Request $request, UserPasswordEncoderInterface $encoder){

        // Récupération du profil
        $userConnected = $this->getUser();
        $userToUpdate = $this->findUser($id);

        // L'utilisateur n'existe pas
        if(!$userToUpdate) $this->notFound();

        // l'utilisateur courant est-il le même que celui qu'on cherche à modifier
        if($userConnected->getId() != $userToUpdate->getId()) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas modifier le profil d'un autre participant.");
        }

        $manager = $this->getDoctrine()->getManager();

        // Création du formulaire
        $form = $this->createForm(ParticipantType::class, $userConnected);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            // On récupère l'entité depuis le formulaire
            $userConnected = $form->getData();

            // Le pseudo existe-t-il déjà en base
            $pseudo = $form->get('pseudo')->getData();
            if($manager->getRepository(Participant::class)->findOneBy(['pseudo' => $pseudo])){
                $this->addFlash("danger", "Ce pseudo existe déjà");
            }

            // On récupère la photo
            $avatar = $form->get('urlPhoto')->getData();

            // Géstion de l'image utilisateur
            if($avatar){

                $originalFilename = pathinfo($avatar->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatar->guessExtension();
                try {
                    $avatar->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash("danger", "Une erreur est survenue");
                    return $this->render('profil/profil-edit.html.twig', [
                        'controller_name' => 'ProfilController',
                        'form' => $form->createView(),
                        'title' => 'Edition de mon profil'
                    ]);
                }
                $userConnected->setUrlPhoto($newFilename);
            }

            // Géstion du mot de passe
            $hashed = $encoder->encodePassword($userConnected, $userConnected->getPassword());
            $userConnected->setPassword($hashed);

            // On persiste le nouvel état de l'entité
            $manager->persist($userConnected);
            $manager->flush();
            $userConnected = $this->findUser($userConnected->getId());

            $this->addFlash("success", "Votre profil a bien été mis à jour");

            return $this->redirectToRoute('profil', [
                'user' => $userConnected,
                'id' => $userConnected->getId()
            ]);
        }

        return $this->render('profil/profil-edit.html.twig', [
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
        return $this->render('bundles/TwigBundle/Exception/error404.html.twig', []);
    }
}
