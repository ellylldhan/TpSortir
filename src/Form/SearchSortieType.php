<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchSortieType extends AbstractType
{

    private $nom;
    private $dateDebut;
    private $dateFin;
    private $campusOrganisateur;
    private $estOrganisateur;
    private $estInscrit;
    private $pasInscrit;
    private $sortiePasse;

    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param mixed $nom
     */
    public function setNom($nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return mixed
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * @param mixed $dateDebut
     */
    public function setDateDebut($dateDebut): void
    {
        $this->dateDebut = $dateDebut;
    }

    /**
     * @return mixed
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * @param mixed $dateFin
     */
    public function setDateFin($dateFin): void
    {
        $this->dateFin = $dateFin;
    }

    /**
     * @return mixed
     */
    public function getCampusOrganisateur()
    {
        return $this->campusOrganisateur;
    }

    /**
     * @param mixed $campusOrganisateur
     */
    public function setCampusOrganisateur($campusOrganisateur): void
    {
        $this->campusOrganisateur = $campusOrganisateur;
    }

    /**
     * @return mixed
     */
    public function getEstOrganisateur()
    {
        return $this->estOrganisateur;
    }

    /**
     * @param mixed $estOrganisateur
     */
    public function setEstOrganisateur($estOrganisateur): void
    {
        $this->estOrganisateur = $estOrganisateur;
    }

    /**
     * @return mixed
     */
    public function getEstInscrit()
    {
        return $this->estInscrit;
    }

    /**
     * @param mixed $estInscrit
     */
    public function setEstInscrit($estInscrit): void
    {
        $this->estInscrit = $estInscrit;
    }

    /**
     * @return mixed
     */
    public function getPasInscrit()
    {
        return $this->pasInscrit;
    }

    /**
     * @param mixed $pasInscrit
     */
    public function setPasInscrit($pasInscrit): void
    {
        $this->pasInscrit = $pasInscrit;
    }

    /**
     * @return mixed
     */
    public function getSortiePasse()
    {
        return $this->sortiePasse;
    }

    /**
     * @param mixed $sortiePasse
     */
    public function setSortiePasse($sortiePasse): void
    {
        $this->sortiePasse = $sortiePasse;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('campusOrganisateur', EntityType::class,
                [
                    'required' => false,
                    'class' => Campus::class,
                    'choice_label' => function ($lieu) {
                        return $lieu->getNom();
                    }
                ])
            ->add('estOrganisateur', CheckboxType::class, [
                'required' => false
            ])
            ->add('estInscrit', CheckboxType::class,  [
                'required' => false
            ])
            ->add('pasInscrit', CheckboxType::class,  [
                'required' => false
            ])
            ->add('sortiePasse', CheckboxType::class, [
                'required' => false
            ])
            ->add('enregister',SubmitType::class,[
                'label' => 'Enregister',
                'attr' => [
                    'class' => 'btn_custom'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchSortieType::class,
        ]);
    }
}
