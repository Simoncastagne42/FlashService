<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\TimeSlot;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableSlots = $options['available_slots'] ?? [];

        $builder
            ->add('timeSlot', EntityType::class, [
                'class' => TimeSlot::class,
                'choices' => $availableSlots,
                'choice_label' => function (TimeSlot $slot) {
                    return $slot->getDate()->format('d/m/Y') . ' - ' .
                           $slot->getHeureDebut()->format('H:i') . ' à ' .
                           $slot->getHeureFin()->format('H:i');
                },
                'label' => 'Choisissez un créneau'
            ]);
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Reservation::class,
            'available_slots' => [],
        ]);
    }
}
