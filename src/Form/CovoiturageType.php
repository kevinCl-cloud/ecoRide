<?php

namespace App\Form;

use App\Entity\Covoiturage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CovoiturageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', IntegerType::class, [
                'label' => 'Prix', 
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('placesNbr', IntegerType::class, [
                'label'=> 'Places disponible',
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('travelTime', TextType::class, [
                'label' => 'Temps de Trajet',
                'attr' => [
                    'placeholder' => 'exemple 2h30'
                ]
            ])
            ->add('departureTime', TimeType::class, [
                'label' => 'Heure de départ'
            ])
            ->add('arrivalTime', TimeType::class, [
                'label' => 'Heure d\'arrivée'
            ])
            ->add('placeDeparture', TextType::class, [
                'label' => 'Ville de départ', 
                    'attr' => [
                        'placeholder' => 'Indiquez la ville de départ'
                    ]
                ]
            )
            ->add('placeArrival', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'attr' => [
                    'placeholder' => 'Indiquez la ville d\'arrivée'
                ]
            ])
            ->add('Validez', SubmitType::class, ['attr' => [
                "class" => "btn-ecoride",
            ]])
        ;

          $builder->get('travelTime')->addModelTransformer(new CallbackTransformer(
            // model -> view (minutes -> "2h30")
            function ($minutes): ?string {
                if ($minutes === null || $minutes === '') {
                    return null;
                }

                // au cas où Doctrine renvoie une string numérique
                $minutes = (int) $minutes;
                if ($minutes < 0) {
                    return null;
                }

                $h = intdiv($minutes, 60);
                $m = $minutes % 60;

                // affiche 2h05 plutôt que 2h5
                return sprintf('%dh%02d', $h, $m);
            },

            // view -> model ("2h30" -> minutes)
            function ($value): ?int {
                if ($value === null || trim((string) $value) === '') {
                    return null;
                }

                $value = strtolower(trim((string) $value));
                // accepte "2h30" ou "2h" ou "2h5"
                if (preg_match('/^(\d+)\s*h\s*(\d{1,2})?$/', $value, $m)) {
                    $hours = (int) $m[1];
                    $mins  = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 0;

                    if ($mins < 0 || $mins > 59) {
                        throw new TransformationFailedException('Minutes invalides. Utilisez par exemple 2h30.');
                    }

                    return ($hours * 60) + $mins;
                }

                throw new TransformationFailedException('Format invalide. Utilisez par exemple 2h30.');
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Covoiturage::class,
        ]);
    }
}
