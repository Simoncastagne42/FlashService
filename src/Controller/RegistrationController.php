<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper, 
        MailerInterface $mailer
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si l'e-mail existe déjà
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $form->get('email')->addError(new FormError('Cette adresse email est déjà utilisée.'));
            } else {
                $user = $form->getData();
                $plainPassword = $form->get('plainPassword')->getData();
                $hashedPassword = $hasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $role = $form->get('role')->getData();
                $user->setRoles([$role]);
                $user->setIsVerified(false); // Important : compte non vérifié au départ

                $entityManager->persist($user);
                $entityManager->flush();

                // Générer le lien de confirmation
                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_email', // Nom de la route de vérification
                    $user->getId(),
                    $user->getEmail(),
                    ['id' => $user->getId()]
                );

                // Créer l'e-mail
                $email = (new Email())
                    ->from('noreply@flashservice.com')
                    ->to($user->getEmail())
                    ->subject('Confirmation de votre adresse e-mail')
                    ->html($this->renderView('emails/confirmation_email.html.twig', [
                        'signedUrl' => $signatureComponents->getSignedUrl(),
                        'expiresAt' => $signatureComponents->getExpiresAt(),
                        'user' => $user,
                    ]));

                // Envoyer l'e-mail
                $mailer->send($email);

                // Ajouter un message flash
                $this->addFlash('success', 'Votre compte a été créé. Veuillez confirmer votre adresse email pour l’activer.');

                // Rediriger vers la page d'accueil ou login
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
