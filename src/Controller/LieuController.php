<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/lieu/add", name="get_modale_lieu")
     */
    public function getModalLieu(Request $request,EntityManagerInterface $em)
    {

        $em = $this->getDoctrine()->getManager();

        $idLieu = $request->get('idLieu');
        $lieuRepository = $em->getRepository('App:Lieu');
dump($idLieu);
        //Il s'ajout d'un ajout de campus
        if ($idLieu == -1) {
            $lieu = new Lieu();
            $title = "Ajouter ";
        } else {
            $lieu = $lieuRepository->findOneBy(['id' => $idLieu]);
            $title = "Modifier ";

            //Si on ne trouve pas le campus
            if (!$lieu) {
                //Création d'une alerte
                $this->addFlash('danger', 'Le lieu n\'existe pas.');
                //Redirection vers la page de gestion des campus
                $this->redirectToRoute('ajout');
            }
        }

        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieu = $form->getData();
            //$lieu->get
            $em->persist($lieu);
            $em->flush();

            $this->addFlash('success', 'Lieu enregistré !');
            return $this->redirectToRoute('ajout');
        }

        return $this->render('lieu/getModalLieu.html.twig',  [
            'title' => $title,
            'idLieu' => $idLieu,
            'formLieu' => $form->createView()
        ]);
    }
}
