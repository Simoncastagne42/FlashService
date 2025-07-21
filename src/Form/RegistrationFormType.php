<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez renseigner un email']),
                    new Assert\Email(['message' => 'L\'email n\'est pas valide']),

                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez renseigner un mot de passe']),
                    new Assert\Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères']),
                ],
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['class' => 'form-input'],
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => ['class' => 'form-input '],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas', // Message en cas de non-correspondance
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Inscription en tant que',
                'choices'  => [
                    'Client' => 'ROLE_CLIENT',
                    'Professionnel' => 'ROLE_PROFESSIONNEL',
                ],
                'mapped' => false, // On le gère manuellement
                'required' => true,
            ])
            ->add('rgpdConsent', CheckboxType::class, [
                'label' => 'J\'accepte la politique de confidentialité',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter notre politique de confidentialité pour vous inscrire.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
