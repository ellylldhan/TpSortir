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
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Doctrine\ORM\QueryBuilder;

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


        $sortie = new SearchSortieType();

        $form = $this->createForm(SearchSortieType::class, $sortie);
        $form->handleRequest($request);

        //Si le formulaire est soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            //On récupère les données et on hydrate l'instance
            $sortie = $form->getData();
        }

        $sorties = $em->getRepository(Sortie::class)->findAllWithLibelle($this->findSearch($sortie, $em));
        dump($sorties);
        return $this->render('sortie/Sortie.html.twig', [
            'formSortie' => $form->createView(),
            'sorties' => $sorties
        ]);
    }

    /**
     * @Route("/ajout", name="ajoutSortie")
     */
    public function ajout(Request $request)
    {


        $em = $this->getDoctrine()->getManager();
        $sortieId = $request->get("sortieId");
        dump($sortieId);
        $sortieRepo = $em->getRepository(Sortie::class);
        $organisateur = $this->getUser();

        if ($sortieId == null) {
            $sortie = new Sortie();
        } else {
            $sortie = $sortieRepo->find($sortieId);
            if ($sortie->getOrganisateur()->getId() != $organisateur->getId()) {
                $this->addFlash('Danger', 'vous n\'avez pas l\'autorisation !');
                return $this->redirectToRoute('sortie');
            }
            if ($sortie->getEtat()->getLibelle() != "Ouverte" && $sortie->getEtat()->getLibelle() != "Fermée") {

            }
            if (!$sortie) {
                //Création d'une alerte contenant le message d'erreur
                $this->addFlash('danger', 'La sortie n\'existe pas.');
                //Redirection vers la page de gestion des campus
                $this->redirectToRoute('sortie');
            }
        }


        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie = $form->getData();
            $sortie->setOrganisateur($organisateur);
            $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
            $sortie->setCampusOrganisateur($organisateur->getCampus());
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Sortie enregistré !');
            return $this->redirectToRoute('sortie');
        }

        return $this->render('sortie/AjoutSortie.html.twig', [
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
        $typeMessage = "error";

        if ($this->getUser() == null) {

        } else {
            $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);
            $participant = $this->getUser();


            $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
            $sortie = $sortieRepo->find($request->get('sortie'));
            $inscriptions = $inscriptionRepo->findBy(["sortie" => $sortie]);
            dump($inscriptions);
            if ($sortie->getDateCloture() > new \DateTime() && count($inscriptions) < $sortie->getNombreInscriptionsMax() && $sortie->getEtat()->getLibelle() == "Ouvert" && $participant != null && $participant->getActif()) {
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
        $this->addFlash($typeMessage, $message);
        return new RedirectResponse($this->generateUrl('sortie'));

    }

    /**
     * @Route("/desinscription", name="desinscriptionSortie")
     */
    public function desinscription(Request $request, EntityManagerInterface $em)
    {


        $message = "désinscription impossible";
        $typeMessage = "error";

        if ($this->getUser() == null) {

        } else {
            $participant = $this->getUser();

            $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
            $sortie = $sortieRepo->find($request->get('sortie'));

            if ($sortie->getEtat()->getLibelle() == "Cloturée" || $sortie->getEtat()->getLibelle() == "Ouvert" || $participant != null || $participant->getActif()) {
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
        $this->addFlash($typeMessage, $message);
        return new RedirectResponse($this->generateUrl('sortie'));
    }

    /**
     * @Route("/annulation", name="annulationSortie")
     */
    public function annulation(Request $request, EntityManagerInterface $em)
    {

        $message = "annulation impossible";
        $typeMessage = "error";

        $participant = $this->getUser();

        $sortie = $em->find(Sortie::class, $request->get('sortie'));

        //vérification si c'est l'organisateur
        if ($sortie->getOrganisateur() == $participant) {
            $sortie->setEtatSortie(1);
            $em->flush();
            $message = "annulation réussi";
            $typeMessage = "succes";
        }
        $this->addFlash($typeMessage, $message);

        //TUDO revoir la redirection
        return new RedirectResponse($this->generateUrl('sortie'));

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
                ->setParameter('pidsub', 2);
            //$this->getUser()->getId()
            // ->setParameter('pid', 2);
        }
        if (!empty($searchSortieType->getEstOrganisateur())) {
            $query = $query
                ->andWhere('o.id = oid')
                //$this->getUser()->getId()
                ->setParameter('oid', 1);
        }
        if (!empty($searchSortieType->getPasInscrit())) {
            $querySub = $em->createQueryBuilder()
                ->select('IDENTITY(isub.sortie)')
                ->from('App:Inscription', 'isub')
                ->where('isub.participant = :pidnotsub');

            $query = $query
                ->andWhere($query->expr()->notIn('s.id', $querySub->getDQL()))
                ->setParameter('pidnotsub', 2);
        }
        if (!empty($searchSortieType->getSortiePasse())) {
            $query = $query
                ->andWhere('e.libelle = \'Passée\'');
        }
        dump($query);
        return $query;
    }

    /**
     * @Route("/updateEtat", name="updatEtat")
     */
    public function UpdateEtat(EntityManagerInterface $em)
    {
        $sorties = $em->getRepository(Sortie::class)
            ->findAllWithEtat();
        $repositoryEtat = $em->getRepository(Etat::class);
        $datenow = new \DateTime();
        foreach ($sorties as $sortie){
            $duree = $sortie->getDuree();
            dump(strval($duree));
            if (!$duree){

                $duree = 0;
            }
            if ($datenow > $sortie->getDateDebut()->add(new \DateInterval('P2Y4DT6H8M'))){
                if ($sortie->getEtat() != 'Passée'){
                    $sortie->setEtat($repositoryEtat->findOneBy(['libelle'=>'Passée']));
                }
            }
            elseif ($datenow > $sortie->getDateDebut() ){
                if ($sortie->getEtat() != 'Activité en cours'){
                    $sortie->setEtat($repositoryEtat->findOneBy(['libelle'=>'Activité en cours']));
                }
            }
            elseif($datenow > $sortie->getDateCloture()){
                if ($sortie->getEtat() != 'Cloturée'){
                    $sortie->setEtat($repositoryEtat->findOneBy(['libelle'=>'Cloturée']));
                }
            }
        }
        return Response::create();
    }

}
