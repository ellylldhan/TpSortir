<?php

namespace App\Controller;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Participant;
use App\Form\ParticipantType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Campus;
use App\Form\CampusType;
use App\Entity\Ville;
use App\Form\VilleType;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin", name="admin")
 */
class AdminController extends AbstractController
{
    /**
     * Permet à un administrateur d'ajouter un participant
     * @Route("/addParticipant", name="_add_participant")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function addParticipant(Request $request,UserPasswordEncoderInterface $encoder)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();
        //Création d'une nouvelle instance de Participant
        $participant = new Participant();

        //Création du formulaire
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        //Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            //On récupère les données et on hydrate l'instance
            $participant = $form->getData();
            //On encode le mot de passe
            $hashed = $encoder->encodePassword($participant, $participant->getPassword());
            $participant->setPassword($hashed);

            //On sauvegarde
            $em->persist($participant);
            $em->flush();

            //On affiche un message de succès et on redirige vers la page d'ajout des participants
            $this->addFlash('success', 'Participant enregistré !');
            $this->redirectToRoute('admin_add_participant');
        } else { //Si le formulaire n'est pas valide
            $errors = $this->getErrorsFromForm($form);

            //Pour chaque erreur, on affiche une alerte contenant le message
            foreach ($errors as $error) {
                $this->addFlash('danger', $error[0]);
            }
        }

        //On redirige vers la page d'ajout
        return $this->render('admin/addParticipant.html.twig', [
            'title' => 'Ajout participant',
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de récupérer la page de gestion des campus
     * @Route("/getCampusPage", name="_get_campus_page")
     * @param Request $request
     * @return mixed
     */
    public function getCampusPage(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //Récupération du repository de l'entité Campus
        $campusRepository = $em->getRepository('App:Campus');
        //On récupère tous les campus ordonnés sur le nom par ordre ascendant
        $toCampus = $campusRepository->findBy(array(), array('nom' => 'ASC'));

        return $this->render('admin/getCampusPage.html.twig', [
            'title' => "Gestion campus",
            'recherche' => null,
            'toCampus' => $toCampus
        ]);
    }

    /**
     * Permet de récupérer la page de gestion des villes
     * @Route("/getVillePage", name="_get_ville_page")
     * @param Request $request
     * @return mixed
     */
    public function getVillePage(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //Récupération du repository de l'entité Ville
        $villeRepository = $em->getRepository('App:Ville');
        //Récupération de toutes les villes ordonnées sur le nom par ordre ascendant
        $toVille = $villeRepository->findBy(array(), array('nom' => 'ASC'));

        return $this->render('admin/getVillePage.html.twig', [
            'title' => "Gestion villes",
            'recherche' => null,
            'toVille' => $toVille
        ]);
    }

    /**
     * Permet de récupérer la page de gestion des participants
     * @Route("/getParticipantPage", name="_get_participant_page")
     * @param Request $request
     * @return mixed
     */
    public function getParticipantPage(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //Récupération du repository de l'entité Participant
        $participantRepository = $em->getRepository('App:Participant');
        //Récupération de tous les participants triés sur le pseudo en ordre ascendant
        $toParticipant = $participantRepository->findBy(array(), array('pseudo' => 'ASC'));

        return $this->render('admin/getParticipantPage.html.twig', [
            'title' => "Gestion participants",
            'toParticipant' => $toParticipant
        ]);
    }

    /**
     * Récupère la modale d'ajout ou de modification
     * @Route("/getModaleCampus", name="_get_modale_campus")
     * @param Request $request
     */
    public function getModaleCampus(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //Récupération de l'identifiant du campus
        $idCampus = $request->get('idCampus');
        //Récupération du repository de l'entité Campus
        $campusRepository = $em->getRepository('App:Campus');

        //Si l'identifiant est égal à -1, il s'ajout d'une création de campus
        if ($idCampus == -1) {
            //Création d'une nouvelle instance de Campus
            $oCampus = new Campus();
            $title = "Ajouter ";
        } else { //Sinon il s'agit d'une modification
            //Récupération du campus existant en fonction de son identifiant
            $oCampus = $campusRepository->findOneBy(['id' => $idCampus]);
            $title = "Modifier ";

            //Si on ne trouve pas le campus
            if (!$oCampus) {
                //Création d'une alerte contenant le message d'erreur
                $this->addFlash('danger', 'Le campus n\'existe pas.');
                //Redirection vers la page de gestion des campus
                $this->redirectToRoute('admin_get_campus_page');
            }
        }

        //Création du formulaire
        $form = $this->createForm(CampusType::class, $oCampus);
        $form->handleRequest($request);

        //Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            //On hydrate l'instance avec les données récupérées
            $oCampus = $form->getData();

            //On sauvegarde en base
            $em->persist($oCampus);
            $em->flush();

            //on affiche un message de succès et on redirige vers la page de gestion des campus
            $this->addFlash('success', 'Campus enregistré !');
            return $this->redirectToRoute('admin_get_campus_page');
        }

        return $this->render('admin/getModaleCampus.html.twig', [
            'title' => $title,
            'idCampus' => $idCampus,
            'form' => $form->createView()
        ]);
    }

    /**
     * Récupère la modale d'ajout ou de modification
     * @Route("/getModaleVille", name="_get_modale_ville")
     * @param Request $request
     */
    public function getModaleVille(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //Récupération de l'identifiant de la ville
        $idVille = $request->get('idVille');
        //Récupération du repository de l'entité Ville
        $villeRepository = $em->getRepository('App:Ville');

        //Si l'identifiant est égal à -1, il s'agit d'une création
        if ($idVille == -1) {
            //On créer une nouvelle instance de Ville
            $oVille = new Ville();
            $title = "Ajouter ";
        } else { //Sinon il s'agit d'une modification
            //on récupère la ville existante en fonction de son identifiant
            $oVille = $villeRepository->findOneBy(['id' => $idVille]);
            $title = "Modifier ";

            //Si on ne trouve pas la ville
            if (!$oVille) {
                //Création d'une alerte
                $this->addFlash('danger', 'La ville n\'existe pas.');
                //Redirection vers la page de gestion des villes
                $this->redirectToRoute('admin_get_ville_page');
            }
        }

        //Création du formulaire
        $form = $this->createForm(VilleType::class, $oVille);
        $form->handleRequest($request);

        //Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            //On hydrate l'entité avec les données du formulaire
            $oVille = $form->getData();

            //On sauvegarde en base
            $em->persist($oVille);
            $em->flush();

            //Affichage du message de succès et redirection vers la page de gestion des villes
            $this->addFlash('success', 'Ville enregistré !');
            return $this->redirectToRoute('admin_get_ville_page');
        }

        return $this->render('admin/getModaleVille.html.twig', [
            'title' => $title,
            'idVille' => $idVille,
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de récupérer la modale de modification d'un participant
     * @Route("/getModaleUpdateParticipant", name="_get_modale_update_participant")
     * @param $idParticipant
     * @return mixed
     */
    public function getModaleUpdateParticipant(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //Récupération de l'identifiant du participant que l'on souhaite modifier
        $idParticipant = $request->get('idParticipant');
        //Récupération du repository de l'entité Participant
        $participantRepository = $em->getRepository('App:Participant');

        //Récupération du participant en fonction de son identifiant
        $oParticipant = $participantRepository->findOneBy(['id' => $idParticipant]);
        //Si on ne trouve pas le participant
        if (!$oParticipant) {
            //On affiche un message d'erreur et on redirige vers la base de gestion des utilisateurs
            $this->addFlash('danger', 'Participant non trouvé.');
            return $this->redirectToRoute('admin_get_participant_page');
        }

        //Définition du formulaire de modification
        $formUpdateParticipant = $this->createFormBuilder($oParticipant)
            ->add('pseudo', TextType::class, ['required' => true, 'attr' => ['class' => 'form-control']])
            ->add('nom', TextType::class, ['required' => true, 'attr' => ['class' => 'form-control']])
            ->add('prenom', TextType::class, ['required' => true, 'attr' => ['class' => 'form-control']])
            ->add('telephone', TextType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
            ->add('mail', TextType::class, ['required' => true, 'attr' => ['class' => 'form-control']])
            ->add('administrateur', CheckboxType::class, ['required' => false, 'attr' => ['class' => 'form-checkbox-input']])
            ->add('actif', CheckboxType::class, ['required' => false, 'attr' => ['class' => 'form-checkbox-input']])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => function ($campus) {
                    return $campus->getNom();
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('enregistrer', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => 'btn_custom']])
            ->getForm();

        $formUpdateParticipant->handleRequest($request);
        //Si le formulaire est soumis et est valide
        if ($formUpdateParticipant->isSubmitted() && $formUpdateParticipant->isValid()) {
            //On hydrate l'instance avec les données du formulaire
            $oParticipant = $formUpdateParticipant->getData();

            //on sauvegarde en base
            $em->persist($oParticipant);
            $em->flush();

            //on affiche un message alertant du succès de la modif et on redirige vers la page de gestion des participants
            $this->addFlash('success', 'Participant modifié !');
            return $this->redirectToRoute('admin_get_participant_page');
        }

        return $this->render('admin/getModaleUpdateParticipant.html.twig', [
            'title' => "Modifier participant",
            'idParticipant' => $idParticipant,
            'form' => $formUpdateParticipant->createView()
        ]);
    }

    /**
     * Permet de rechercher un campus sur son nom
     * @Route("/searchCampus", name="_search_campus")
     * @param Request $request
     * @return mixed
     */
    public function searchCampus(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();
        //Récupération du repository de l'entité Campus
        $campusRepository = $em->getRepository('App:Campus');

        //On récupère ce que l'utilisateur recherche
        $recherche = $request->get('campus_recherche');
        //Si on n'a pas de recherche, on redirige vers la page de gestion des campus
        if (!$recherche) {
            return $this->redirectToRoute('admin_get_campus_page');
        }

        //On récupère les campus correspondant à la recherche
        $toCampus = $campusRepository->searchCampus($recherche);

        return $this->render('admin/getCampusPage.html.twig', [
            'title' => "Gestion campus",
            'recherche' => $recherche,
            'toCampus' => $toCampus
        ]);
    }

    /**
     * Permet de rechercher une ville sur son nom
     * @Route("/searchVille", name="_search_ville")
     * @param Request $request
     * @return mixed
     */
    public function searchVille(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();
        //Récupération du repository de l'entité Ville
        $villeRepository = $em->getRepository('App:Ville');

        //On récupère la recherche de l'utilisateur
        $recherche = $request->get('ville_recherche');
        //Si on n'a pas de recherche
        if (!$recherche) {
            //On redirige vers la page de gestion des villes
            return $this->redirectToRoute('admin_get_ville_page');
        }

        //Récupération des villes en fonction de la recherche utilisateur
        $toVille = $villeRepository->searchVille($recherche);

        return $this->render('admin/getVillePage.html.twig', [
            'title' => "Gestion villes",
            'recherche' => $recherche,
            'toVille' => $toVille
        ]);
    }

    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
