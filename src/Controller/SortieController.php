<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ParticipantType;
use App\Form\SearchSortieType;
use App\Form\SortieType;
use App\Manager\SortieManager;
use Couchbase\SearchSort;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Doctrine\ORM\QueryBuilder;
use Mobile_Detect;

/**
 * @Route("/sortie")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/", name="sortie")
     */
    public function sortie(Request $request, EntityManagerInterface $em)
    {
        $detect = new Mobile_Detect();
        $sortie = new SearchSortieType();

        $form = $this->createForm(SearchSortieType::class, $sortie, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);

        //Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            //On récupère les données et on hydrate l'instance
            $sortie = $form->getData();
        }

        $sorties = $em->getRepository(Sortie::class)->findAllWithLibelle($this->findSearch($sortie, $em));
        ////dump($detect->isMobile());
        return $this->render('sortie/sortie.html.twig', [
            'formSortie' => $form->createView(),
            'sorties' => $sorties,
            'isMobile' => $detect->isMobile()
        ]);
    }

    /**
     * @Route("/ajout", name="ajoutSortie")
     */
    public function ajout(Request $request)
    {

        $detect = new Mobile_Detect;
        if ($detect->isMobile() == true) {

            return $this->redirectToRoute('sortie');
        }

        $em = $this->getDoctrine()->getManager();
        $sortieId = $request->get("sortieId");

        $sortieRepo = $em->getRepository(Sortie::class);
        $organisateur = $this->getUser();

        if ($sortieId == null) {
            $sortie = new Sortie();
        } else {
            $sortie = $sortieRepo->find($sortieId);
            if (!$sortie) {
                //Création d'une alerte contenant le message d'erreur
                $this->addFlash('danger', 'La sortie n\'existe pas.');
                //Redirection vers la page de gestion des campus
                $this->redirectToRoute('sortie');
            }
            $this->updateEtat($em, $sortie);

            if ($sortie->getOrganisateur()->getId() != $organisateur->getId()) {
                $this->addFlash('danger', 'vous n\'avez pas l\'autorisation !');
                return $this->redirectToRoute('sortie');
            }

            if ($sortie->getEtat()->getLibelle() != 'Créee'){
                $this->addFlash('danger', 'La sortie ne peux pas être modifé !');
                return $this->redirectToRoute('sortie');
            }
        }


        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($organisateur->getActif()) {
            if ($form->isSubmitted() && $form->isValid() && ($form->get('publication')->isClicked() || $form->get('enregister')->isClicked() )) {

                $sortie = $form->getData();
                $sortie->setOrganisateur($organisateur);

                if ($form->get('publication')->isClicked() ) {
                    $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créee']));
                }

                if ($form->get('enregister')->isClicked()){
                    $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
                }

                $sortie->setCampusOrganisateur($organisateur->getCampus());
                $em->persist($sortie);
                $em->flush();

                $this->addFlash('success', 'Sortie enregistré !');
                return $this->redirectToRoute('sortie');
            }
            else { //Si le formulaire n'est pas valide
                $errors = $this->getErrorsFromForm($form);

                //Pour chaque erreur, on affiche une alerte contenant le message
                foreach ($errors as $error) {
                    $this->addFlash("danger", $error[0]);
                }
            }
        } else {
            $this->addFlash('danger', 'Organisateur non Actif !');
        }

        return $this->render('sortie/ajoutSortie.html.twig', [
            'campusName' => $this->getUser()->getCampus()->getNom(),
            'formSortie' => $form->createView()
        ]);
    }

    /**
     * @Route("/inscription", name="inscriptionSortie")
     */
    public function inscription(Request $request, EntityManagerInterface $em)
    {
        $message = "inscription impossible";
        $typeMessage = "danger";

        if ($this->getUser() == null) {

        } else {
            $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);
            $participant = $this->getUser();


            $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
            $sortie = $sortieRepo->find($request->get('sortieId'));
            $inscriptions = $inscriptionRepo->findBy(["sortie" => $sortie]);
            if ($sortie) {
                $this->updateEtat($em, $sortie);

                if ($participant != null && $participant->getActif() && $sortie->getDateCloture() > new \DateTime() && count($inscriptions) < $sortie->getNombreInscriptionsMax() && $sortie->getEtat()->getLibelle() == "Ouverte") {
                    $inscription = new Inscription();
                    $inscription->setDateInscription(new \DateTime());
                    $inscription->setParticipant($participant);
                    $inscription->setSortie($sortie);
                    $em->persist($inscription);
                    $em->flush();
                    $message = "inscription réussi";
                    $typeMessage = "success";
                }
            }


        }
        $this->addFlash($typeMessage, $message);
        return new RedirectResponse($this->generateUrl('sortie'));

    }

    /**
     * @Route("/desinscription", name="desinscriptionSortie")
     */
    public function desinscription(Request $request, EntityManagerInterface $em)
    {
        $message = "désinscription impossible";
        $typeMessage = "danger";

        if ($this->getUser()) {
            $participant = $this->getUser();

            $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
            $sortie = $sortieRepo->find($request->get('sortieId'));
            if ($sortie) {
                $this->updateEtat($em, $sortie);
                if ($participant != null && $participant->getActif() && ($sortie->getEtat()->getLibelle() == "Cloturée" || $sortie->getEtat()->getLibelle() == "Ouverte")) {
                    $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);

                    //$inscription = $inscriptionRepo->findOneBy(["sortie"=>$sortie,"participant"=>$participant]);
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
        }
        $this->addFlash($typeMessage, $message);
        return new RedirectResponse($this->generateUrl('sortie'));
    }

    /**
     * @Route("/annulation", name="annulationSortie")
     */
    public function annulation(Request $request, EntityManagerInterface $em)
    {

        $message = "annulation impossible";
        $typeMessage = "danger";
        $motif = $request->get('motif');
        $participant = $this->getUser();

        $sortie = $em->find(Sortie::class, $request->get('sortieId'));
        if ($sortie && $motif) {

            $this->updateEtat($em, $sortie);

            if ($participant != null && $participant->getActif() && ($sortie->getOrganisateur()->getId() == $participant->getId() || $participant->getAdministrateur() == true) && ($sortie->getEtat()->getLibelle() == 'Cloturée' || $sortie->getEtat()->getLibelle() == 'Ouverte' || $sortie->getEtat()->getLibelle() == 'Créee')) {
                $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']));
                $sortie->setDescriptionInfo($motif);
                $em->flush();
                $message = "annulation réussi";
                $typeMessage = "success";
            }
        }

        $this->addFlash($typeMessage, $message);

        return new RedirectResponse($this->generateUrl('sortie'));
    }

    /**
     * @Route("/detail", name="detail")
     */
    public function getSortie(Request $request, EntityManagerInterface $em)
    {

        $em = $this->getDoctrine()->getManager();
        $sortieRepository = $em->getRepository('App:Sortie');

        $sortieid = $request->get('sortieId');

        if (!$sortieid) {
            //On redirige vers la page de gestion des villes
            $this->addFlash('danger', 'aucune sortie trouvée');
            return $this->redirectToRoute('sortie');
        }

        //Récupération des villes en fonction de la recherche utilisateur
        $sortie = $sortieRepository->find($sortieid);

        return $this->render('sortie/getSortie.html.twig', [
            'sortie' => $sortie
        ]);
    }

    /**
     * @Route("/modalAnnuler", name="modalAnnuler")
     */
    public function getModalAnnuler(Request $request, EntityManagerInterface $em)
    {

        $sortieid = $request->get('sortieId');

        return $this->render('sortie/getModalSortieAnnuler.html.twig', [
            'sortieId' => $request->get('sortieId')
        ]);
    }

    /**
     * @Route("/updateEtat", name="updatEtat")
     */
    public function ManagerUpdateEtat(EntityManagerInterface $em, Sortie $sortie = null)
    {
        $sorties = $em->getRepository(Sortie::class)
            ->findAllWithEtat();
        if ($sortie) {
            $this->updateEtat($em, $sortie);
        } else {
            foreach ($sorties as $sortie) {
                $this->updateEtat($em, $sortie);
            }
        }
        return Response::create();
    }

    public function updateEtat(EntityManagerInterface $em, Sortie $sortie)
    {
        $repositoryEtat = $em->getRepository(Etat::class);
        $datenow = new \DateTime();
        $duree = $sortie->getDuree();
        if (!$duree) {
            $duree = 0;
        }
        if ($sortie->getEtat()->getLibelle() != 'Créee') {
            if ($datenow > $sortie->getDateDebut()->add(new \DateInterval('P0Y0DT0H' . $duree . 'M'))) {
                if ($sortie->getEtat() != 'Passée') {
                    $sortie->setEtat($repositoryEtat->findOneBy(['libelle' => 'Passée']));
                }
            } elseif ($datenow > $sortie->getDateDebut()) {
                if ($sortie->getEtat() != 'Activité en cours') {
                    $sortie->setEtat($repositoryEtat->findOneBy(['libelle' => 'Activité en cours']));
                }
            } elseif ($datenow > $sortie->getDateCloture()) {
                if ($sortie->getEtat() != 'Cloturée') {
                    $sortie->setEtat($repositoryEtat->findOneBy(['libelle' => 'Cloturée']));
                }
            }
            $em->persist($sortie);
            $em->flush();
        }
    }

    public function findSearch(SearchSortieType $searchSortieType, EntityManagerInterface $em)
    {

        $query = $em->createQueryBuilder('s');

        if (!empty($searchSortieType->getNom())) {
            $query = $query
                ->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', "%{$searchSortieType->getNom()}%");
        }

        if (!empty($searchSortieType->getDateDebut())) {
            $query = $query
                ->andWhere('s.dateDebut >= :dtdeb')
                ->setParameter('dtdeb', $searchSortieType->getDateDebut());
        }

        if (!empty($searchSortieType->getDateFin())) {
            $query = $query
                ->andWhere('s.dateDebut <= :dtfin')
                ->setParameter('dtfin', $searchSortieType->getDateFin());
        }

        if (!empty($searchSortieType->getCampusOrganisateur())) {
            $query = $query
                ->andWhere('co.id = :coid')
                ->setParameter('coid', $searchSortieType->getCampusOrganisateur()->getId());
        }

        if (!empty($searchSortieType->getEstInscrit())) {
            $querySub = $em->createQueryBuilder()
                ->select('IDENTITY(isub.sortie)')
                ->from('App:Inscription', 'isub')
                ->where('isub.participant = :pidsub');


            $query = $query
                ->andWhere($query->expr()->In('s.id', $querySub->getDQL()))
                ->setParameter('pidsub', $this->getUser()->getId());
            //$this->getUser()->getId()
            // ->setParameter('pid', 2);
        }
        if (!empty($searchSortieType->getEstOrganisateur())) {
            //dump($this->getUser()->getId());
            $query = $query
                ->andWhere('o.id = :oid')
                ->setParameter('oid', $this->getUser()->getId());
        }
        if (!empty($searchSortieType->getPasInscrit())) {
            $querySub = $em->createQueryBuilder()
                ->select('IDENTITY(isub.sortie)')
                ->from('App:Inscription', 'isub')
                ->where('isub.participant = :pidnotsub');

            $query = $query
                ->andWhere($query->expr()->notIn('s.id', $querySub->getDQL()))
                ->setParameter('pidnotsub', $this->getUser()->getId());
        }
        if (!empty($searchSortieType->getSortiePasse())) {
            $query = $query
                ->andWhere('e.libelle = \'Passée\'');
        }
        //dump($query);
        return $query;
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
