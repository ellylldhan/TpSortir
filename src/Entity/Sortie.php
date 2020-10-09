<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThanOrEqual(
     *     propertyPath="dateCloture"
     * )
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCloture;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombreInscriptionsMax;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $descriptionInfo;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campusOrganisateur;

    /**
     * @ORM\OneToMany(targetEntity=Inscription::class, mappedBy="sortie")
     */
    private $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateCloture(): ?\DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(\DateTimeInterface $dateCloture): self
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getNombreInscriptionsMax(): ?int
    {
        return $this->nombreInscriptionsMax;
    }

    public function setNombreInscriptionsMax(int $nombreInscriptionsMax): self
    {
        $this->nombreInscriptionsMax = $nombreInscriptionsMax;

        return $this;
    }

    public function getDescriptionInfo(): ?string
    {
        return $this->descriptionInfo;
    }

    public function setDescriptionInfo(?string $descriptionInfo): self
    {
        $this->descriptionInfo = $descriptionInfo;

        return $this;
    }


    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCampusOrganisateur(): ?Campus
    {
        return $this->campusOrganisateur;
    }

    public function setCampusOrganisateur(?Campus $campusOrganisateur): self
    {
        $this->campusOrganisateur = $campusOrganisateur;

        return $this;
    }

    /**
     * @return Collection|Inscription[]
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions[] = $inscription;
            $inscription->setSortie($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->contains($inscription)) {
            $this->inscriptions->removeElement($inscription);
            // set the owning side to null (unless already changed)
            if ($inscription->getSortie() === $this) {
                $inscription->setSortie(null);
            }
        }

        return $this;
    }
}
