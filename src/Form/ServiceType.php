<?php

namespace App\Form;

use App\Entity\Professional;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du Service',
                'attr' => [
                    'class' => 'mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md'
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix ',
                'attr' => [
                    'class' => 'mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md '
                ],
                'currency' => '(EUR)',  // Exemple pour une devise en euro
            ])
            ->add('professional', EntityType::class, [
                'class' => Professional::class,
                'choice_label' => 'name',  // Ou tout autre champ qui définit le nom du professionnel
                'label' => 'Professionnel',
                'attr' => [
                    'class' => 'mt-1 block w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md'
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 focus:outline-none'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
