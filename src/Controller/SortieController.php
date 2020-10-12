<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ParticipantType;
use App\Form\SortieType;
use App\Manager\SortieManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sortie")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/ajout", name="ajout")
     */
    public function ajout(Request $request,EntityManagerInterface $em)
    {

        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();
        //Création d'une nouvelle instance de Participant
        $sortie = new Sortie();
        //Création du formulaire
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        //Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            //On récupère les données et on hydrate l'instance
            $sortie = $form->getData();
           //pseudo_5_2
            // $sortie->setOrganisateur($em->getRepository(Participant::class)->findOneBy(['pseudo' => $this->getUser()->getUsername()]));
            $organisateur = $em->getRepository(Participant::class)->findOneBy(['pseudo' => 'pseudo_5_2']);
            $sortie->setOrganisateur($organisateur);
            $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle'=>'Ouverte']));
            $sortie->setCampusOrganisateur($organisateur->getCampus());

            //On sauvegarde
            $em->persist($sortie);
            $em->flush();

            //On affiche un message de succès et on redirige vers la page d'ajout des participants
            $this->addFlash('success', 'Sortie enregistré !');
            $form = $this->createForm(SortieType::class, $sortie);

        }

        //On redirige vers la page d'ajout
        return $this->render('sortie/AjoutSortie.html.twig', [
            'campusName' => $em->getRepository(Participant::class)->findOneBy(['pseudo' => 'pseudo_5_2'])->getCampus()->getNom(),
            'formSortie' => $form->createView()
        ]);
    }

    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request, EntityManagerInterface $em)
    {
        $message = "inscription impossible";
        $typeMessage = "error";

        if ($this->getUser() == null) {

        } else {
            $participantRepo = $this->getDoctrine()->getRepository(Participant::class);
            $participant = $participantRepo->findBy(['pseudo' => $this->getUser()->getUsername()]);


            $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
            $sortie = $sortieRepo->find($request->get('sortie'));

            if ($sortie->getDateCloture() > new DateTime() || $sortie->getEtatSortie() == 0 || $participant != null || $participant->getActif()) {
                $inscription = new Inscription();
                $inscription->setDateInscription(new DateTime());
                $inscription->setParticipant($participant);
                $inscription->setDateInscription($sortie);
                $em->persist();
                $em->flush();
                $message = "inscription réussi";
                $typeMessage = "success";
            }


        }
        $this->addFlash($typeMessage, $message);
        return new RedirectResponse($this->generateUrl('sortie'));

    }

    /**
     * @Route("/desinscription", name="desinscription")
     */
    public function desinscription(Request $request, EntityManagerInterface $em)
    {
        $message = "désinscription impossible";
        $typeMessage = "error";

        if ($this->getUser() == null) {

        } else {
            $participantRepo = $this->getDoctrine()->getRepository(Participant::class);
            $participant = $participantRepo->findBy(['pseudo' => $this->getUser()->getUsername()]);

            $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);

            $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
            $sortie = $sortieRepo->find($request->get('sortie'));

            if ($sortie->getDateCloture() > new DateTime() || $sortie->getEtatSortie() == 0 || $participant != null || $participant->getActif()) {
                $inscription = new Inscription();
                $inscription->setDateInscription(new DateTime());
                $inscription->setParticipant($participant);
                $inscription->setDateInscription($sortie);
                $em->remove($inscriptionRepo->find([
                    'participant' => $participant,
                    'sortie' => $sortie
                ])
                );
                $em->flush();
                $message = "désinscription réussi";
                $typeMessage = "success";
            }


        }
        $this->addFlash($typeMessage, $message);
        return new RedirectResponse($this->generateUrl('sortie'));
    }

    /**
     * @Route("/annulation", name="annulation")
     */
    public function annulation(Request $request, EntityManagerInterface $em)
    {
        $message = "annulation impossible";
        $typeMessage = "error";

        $participantRepo = $this->getDoctrine()->getRepository(Participant::class);
        $participant = $participantRepo->findBy(['pseudo' => $this->getUser()->getUsername()]);

        $sortie = $em->find(Sortie::class,$request->get('sortie'));

        //vérification si c'est l'organisateur
        if ($sortie->getOrganisateur() == $participant) {
            $sortie->setEtatSortie(1);
            $em.flush();
            $message = "annulation réussi";
            $typeMessage = "succes";
        }
        $this->addFlash($typeMessage, $message);

        //TUDO revoir la redirection
        return new RedirectResponse($this->generateUrl('sortie'));

    }
}
