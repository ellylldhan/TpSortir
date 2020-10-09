<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 30
                ]
            ])
            ->add('dateDebut', TextType::class)
            ->add('duree',IntegerType::class)
            ->add('dateCloture', TextType::class)
            ->add('nombreInscriptionsMax')
            ->add('descriptionInfo',TextareaType::class)
            ->add('lieu',EntityType::class,[
                'required' => true,
                'class' => Lieu::class,
                'choice_label' => function($lieu){
                    return $lieu->getNom();
                }])
            ->add('enregister',SubmitType::class,[
                'label' => 'Enregister',
                'attr' => ['class' => 'btn_custom']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
