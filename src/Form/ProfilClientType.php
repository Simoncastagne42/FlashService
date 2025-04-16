<?php


namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('zip_code', TextType::class, [
                'label' => 'Code postal',
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
            ])
            ->add('birth_date', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
