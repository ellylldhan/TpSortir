<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class,[
                'attr' => ['class' => 'form_control' ]
            ])
            ->add('rue',TextType::class,[
                'attr' => ['class' => 'form_control' ]
            ])
            ->add('latitude',IntegerType::class,[
                'required' => false,
                'attr' => ['class' => 'form_control' ]
            ])
            ->add('longitude',IntegerType::class,[
                'required' => false,
                'attr' => ['class' => 'form_control' ]
            ])
            ->add('ville',EntityType::class,[
                'required' => true,
                'class' => Ville::class,
                'choice_label' => function($ville){
                    return $ville->getNom();
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('enregister',SubmitType::class,[
                'label' => 'Enregister',
                'attr' => ['class' => 'btn_custom']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
