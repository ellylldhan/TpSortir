<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Entity\Campus;
use App\Form\CampusType;

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
     * @return
     */
    public function addParticipant(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $participant= new Participant();

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant = $form->getData();

            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'Participant enregistré !');
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
     */
    public function getCampusPage(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $campusRepository = $em->getRepository('App:Campus');
        $toCampus = $campusRepository->findAll();

        return $this->render('admin/getCampusPage.html.twig', [
            'title' => "Gestion campus",
            'recherche' => null,
            'toCampus' => $toCampus
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
