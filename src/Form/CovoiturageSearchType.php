<?php

namespace App\Form;

use App\Model\CovoiturageSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CovoiturageSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('placeDeparture', SearchType::class, [
                'label'=> false,
                'attr' => [
                    'class' => 'form-form-control search-input',
                    'placeholder' => 'Ville de départ'
                ]
            ])
            ->add('placeArrival', SearchType::class, [
                'label'=> false,
                'attr' => [
                    'class' => 'form-form-control search-input',
                    'placeholder' => 'Ville d\'arrivée'
                ]
            ])
            ->add('dateDeparture', DateType::class, [
                'label'=> false,
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control search-input',
                    'placeholder' => 'Date de départ'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CovoiturageSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
