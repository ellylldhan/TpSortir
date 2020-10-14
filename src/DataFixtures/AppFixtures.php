<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $campusRepository;
    private $etatRepository;
    private $lieuRepository;
    private $encoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->campusRepository = $em->getRepository(Campus::class);
        $this->etatRepository = $em->getRepository(Etat::class);
        $this->lieuRepository = $em->getRepository(Lieu::class);
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();

        // Types d'Etats
        $typesEtat = [
            "Créee",
            "Ouverte",
            "Cloturée",
            "Activité en cours",
            "Passée",
            "Annulée"
        ];

        // Types de lieux
        $typesLieu = [
            "Piscine",
            "Parc",
            "Centre aéré",
            "Circuit",
            "Stade"
        ];

        // Types de campus
        $typesCampus = [
            "Campus de Thomas",
            "Campus de Loan",
            "Campus de Reno",
            "Campus de Guiom"
        ];

        // Génération des Etats
        for($i=0;$i<count($typesEtat);$i++){
            $etat = new Etat();
            $etat->setLibelle($typesEtat[$i]);
            $manager->persist($etat);
        }
        $manager->flush();

        // Génération des campus
        for($i=0;$i<count($typesCampus);$i++){
            $campus = new Campus();
            $campus->setNom($typesCampus[$i]);
            $manager->persist($campus);
        }
        $manager->flush();

        // Génération des Villes & Lieux avec association
        for($i=0;$i<=20;$i++){
            ////////
            $ville = new Ville();
            $ville->setNom($faker->city())
                ->setCodePostal($faker->postcode());

            for($j=0;$j<2;$j++){
                $lieu = new Lieu();
                $streetName = $faker->streetName();
                try {
                    $lieu->setNom(
                        $typesLieu[random_int(0, count($typesLieu)-1)]." ".$streetName
                    );
                } catch (Exception $e) {
                    print_r($e);
                }
                $lieu->setRue($streetName)
                    ->setLatitude(
                        $faker->latitude($min = -90, $max = 90)
                    )
                    ->setLongitude(
                        $faker->longitude($min = -180, $max = 180)
                    );
                $ville->addLieu($lieu);
                $manager->persist($lieu);
            }
            $manager->persist($ville);
            $manager->flush();
        }

        // Récupération des collections
        $campusCollection = $this->campusRepository->findAll();
        $etatsCollection = $this->etatRepository->findAll();
        $lieuxCollection = $this->lieuRepository->findAll();

        foreach ($campusCollection as $key => $campus){

            // Organisateur
            $organisateur = new Participant();
            $organisateur->setPrenom($faker->firstName())
                ->setNom($faker->name())
                ->setMail($faker->email())
                ->setMotDePasse($this->encoder->encodePassword($organisateur, "password"))
                ->setPseudo("organisateur_".$key)
                ->setActif($faker->boolean())
                ->setAdministrateur($faker->boolean())
                ->setTelephone("0102030405")
                ->setCampus($campus)
                ->setUrlPhoto("https://via.placeholder.com/100.png");

            $manager->persist($organisateur);

            // Sorties
            $sortie = new Sortie();
            $ts = $faker->unixTime($max = 'now');
            $sortie->setNom("Sortie ".$key)
                ->setDateDebut(new \DateTime())
                ->setDateCloture(new \DateTime(date("d-m-Y", $ts+random_int(0, 10))))
                ->setUrlPhoto("https://via.placeholder.com/100.png")
                ->setEtat($etatsCollection[random_int(0, count($etatsCollection)-1)])
                ->setCampusOrganisateur($campus)
                ->setLieu($lieuxCollection[random_int(0, count($lieuxCollection)-1)])
                ->setOrganisateur($organisateur)
                ->setNombreInscriptionsMax(20);

            $manager->persist($sortie);

            for($i=0;$i<20;$i++){
                // Participant
                $participant = new Participant();
                $participant->setPrenom($faker->firstName())
                    ->setNom($faker->name())
                    ->setMail($faker->email())
                    ->setMotDePasse($this->encoder->encodePassword($participant, "password"))
                    ->setPseudo("pseudo_".$campus->getId()."_".$i)
                    ->setActif($faker->boolean())
                    ->setAdministrateur($faker->boolean())
                    ->setTelephone("0102030405")
                    ->setCampus($campus)
                    ->setUrlPhoto("https://via.placeholder.com/100.png");

                $manager->persist($participant);

                $inscription = new Inscription();
                $inscription->setDateInscription(new \DateTime($faker->date($format = 'd-m-Y', $max = 'now')))
                    ->setParticipant($participant)
                    ->setSortie($sortie);

                $manager->persist($inscription);
            }
            $manager->persist($campus);
        }
        $manager->flush();
    }
}
