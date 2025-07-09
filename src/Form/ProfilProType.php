<?php

namespace App\Form;

use App\Entity\Professional;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Profession;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilProType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('compagny_name', TextType::class, [
                'label' => 'Nom de l’entreprise',
            ])
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('adress_compagny', TextType::class, [
                'label' => "Adresse de l'entreprise",
            ])
            ->add('city_compagny', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('zip_code_compagny', TextType::class, [
                'label' => 'Code postal',
            ])
            ->add('phone_compagny', TelType::class, [
                'label' => 'Téléphone',
            ])
            ->add('siret', TextType::class, [
                'label' => 'Numéro SIRET',
            ])
            ->add('profession', EntityType::class, [
                'class' => Profession::class,
                'choice_label' => 'name',
                'label' => 'Profession',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Professional::class,
        ]);
    }
}
