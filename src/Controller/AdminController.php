<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
        $em = $this->getDoctrine()->getManager();
        $participant = new Participant();

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant = $form->getData();
            $hashed = $encoder->encodePassword($participant, $participant->getPassword());
            $participant->setPassword($hashed);

            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'Participant enregistré !');
            $this->redirectToRoute('admin_add_participant');
        } else {
            $errors = $this->getErrorsFromForm($form);

            foreach ($errors as $error) {
                $this->addFlash('danger', $error[0]);
            }
        }

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
        $em = $this->getDoctrine()->getManager();

        $campusRepository = $em->getRepository('App:Campus');
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
        $em = $this->getDoctrine()->getManager();

        $villeRepository = $em->getRepository('App:Ville');
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
        $em = $this->getDoctrine()->getManager();

        $participantRepository = $em->getRepository('App:Participant');
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
        $em = $this->getDoctrine()->getManager();

        $idCampus = $request->get('idCampus');
        $campusRepository = $em->getRepository('App:Campus');

        //Il s'ajout d'un ajout de campus
        if ($idCampus == -1) {
            $oCampus = new Campus();
            $title = "Ajouter ";
        } else {
            $oCampus = $campusRepository->findOneBy(['id' => $idCampus]);
            $title = "Modifier ";

            //Si on ne trouve pas le campus
            if (!$oCampus) {
                //Création d'une alerte
                $this->addFlash('danger', 'Le campus n\'existe pas.');
                //Redirection vers la page de gestion des campus
                $this->redirectToRoute('admin_get_campus_page');
            }
        }

        $form = $this->createForm(CampusType::class, $oCampus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oCampus = $form->getData();

            $em->persist($oCampus);
            $em->flush();

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
        $em = $this->getDoctrine()->getManager();

        $idVille = $request->get('idVille');
        $villeRepository = $em->getRepository('App:Ville');

        //Il s'ajout d'un ajout de campus
        if ($idVille == -1) {
            $oVille = new Ville();
            $title = "Ajouter ";
        } else {
            $oVille = $villeRepository->findOneBy(['id' => $idVille]);
            $title = "Modifier ";

            //Si on ne trouve pas le campus
            if (!$oVille) {
                //Création d'une alerte
                $this->addFlash('danger', 'La ville n\'existe pas.');
                //Redirection vers la page de gestion des campus
                $this->redirectToRoute('admin_get_ville_page');
            }
        }

        $form = $this->createForm(VilleType::class, $oVille);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oCampus = $form->getData();

            $em->persist($oVille);
            $em->flush();

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
        $em = $this->getDoctrine()->getManager();
        $participantRepository = $em->getRepository('App:Participant');

        $idParticipant = $request->get('idParticipant');

        $oParticipant = $participantRepository->findOneBy(['id' => $idParticipant]);
        if (!$oParticipant) {
            $this->addFlash('danger', 'Participant non trouvé.');
            return $this->redirectToRoute('admin_get_participant_page');
        }

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
        if ($formUpdateParticipant->isSubmitted() && $formUpdateParticipant->isValid()) {
            $oParticipant = $formUpdateParticipant->getData();

            $em->persist($oParticipant);
            $em->flush();

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
        $em = $this->getDoctrine()->getManager();
        $campusRepository = $em->getRepository('App:Campus');

        $recherche = $request->get('campus_recherche');
        if (!$recherche) {
            return $this->redirectToRoute('admin_get_campus_page');
        }

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
        $em = $this->getDoctrine()->getManager();
        $villeRepository = $em->getRepository('App:Ville');

        $recherche = $request->get('ville_recherche');
        if (!$recherche) {
            return $this->redirectToRoute('admin_get_ville_page');
        }

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
