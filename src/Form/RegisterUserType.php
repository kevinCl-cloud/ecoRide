<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RegisterUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [ 
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom'
                ]
            ])
            ->add('firstName', TextType::class, [ 
                'label' => 'Prenom',
                'attr' => [
                    'placeholder' => 'Entrez votre prenom'
                    ]
            ])
            ->add('pseudo', TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrez votre pseudo'
                    ]
            ])
            ->add('photo', FileType::class, [
                    'label' => 'Photo de profil',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/webp'
                            ],
                            'mimeTypesMessage' => 'Veuillez uploader une image valide (jpg, png, webp)',
                        ])
                    ],
                ])

            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'exemple@email.com'
                    ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule.'
                    ]),
                ],
                'first_options'  => [
                    'label' => 'Mot de passe', 'hash_property_path' => 'password',
                    'attr' => [
                    'placeholder' => 'Minimum 8 caractères'
                    ]
                    ],
                    'second_options' => ['label' => 'Confirmez le mot de passe',
                    'attr' => [
                        'placeholder' => 'Répétez votre mot de passe'
                    ]
                    ],
                    'mapped' => false,
            ])
            ->add('Validez', SubmitType::class, ['attr' => [
                "class" => "btn-ecoride",
            ]])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
