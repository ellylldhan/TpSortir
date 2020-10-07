<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Manager\SortieManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie", name="sortie")
     */
    public function index()
    {
        return $this->render('sortie/Test.html.twig');
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
