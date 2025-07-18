<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\TimeSlot;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('timeSlot', EntityType::class, [
                'class' => TimeSlot::class,
                'choice_label' => function ($slot) {
                    return sprintf(
                        '%s de %s à %s',
                        $slot->getDate()->format('d/m/Y'),
                        $slot->getHeureDebut()->format('H:i'),
                        $slot->getHeureFin()->format('H:i')
                    );
                },
                'placeholder' => 'Choisissez un créneau',
                'label' => 'Créneau disponible',
                'group_by' => fn($slot) => $slot->getDate()->format('d/m/Y'),
                'choices' => $options['available_slots'] ?? [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Reservation::class,
            'available_slots' => [],
        ]);
    }
}
