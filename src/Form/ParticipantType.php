<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 30
                ]
            ])
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 30
                ]
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 30
                ]
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'max-length' => 15
                ]
            ])
            ->add('mail', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max-length' => 20
                ]
            ])
            ->add('motDePasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit être identique.',
                'options' => ['attr' => [
                    'class' => 'password-field form-control',
                    'placeholder' => 'Mot de passe'
                ]],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
            ])
            ->add('administrateur', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-checkbox-input'
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-checkbox-input'
                ]
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => function ($campus) {
                    return $campus->getNom();
                },
                'attr' => [
                    'class' => 'form-control',
                    'label' => 'campus'
                ]
            ])
            ->add('urlPhoto', FileType::class, [
                'label' => 'Photo participant',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'btn btn-primary btn-sm'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Veuillez charger un fichier de type pdf, jpeg ou gif',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
