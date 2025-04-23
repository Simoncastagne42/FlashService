<?php


namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProfilClientType;
use App\Form\ProfilProType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Professional;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;




final class ProfilController extends AbstractController

{
    #[Route('/my-account', name: 'app_my_account')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myAccount(): Response
    {
        return $this->render('account/base_my_account.html.twig');
    }


    #[Route('/my-account/profil-infos', name: 'app_profil_infos')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {

        /**
         * @var User $user
         */


        $user = $this->getUser();

        if (in_array('ROLE_PROFESSIONNEL', $user->getRoles())) {


            $pro = $user->getProfessional();
            if (!$pro) {
                $pro = new Professional();
                $pro->setUser($user);
                $entityManager->persist($pro);
            }
            $isPro = true;
            $isClient = false;
            $form = $this->createForm(ProfilProType::class, $pro);
        } else {
            $client = $user->getClient();
            if (!$client) {
                $client = new Client();
                $client->setUser($user);
                $entityManager->persist($client);
            }
            $isClient = true;
            $isPro = false;
            $form = $this->createForm(ProfilClientType::class, $client);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profil_infos');
        }

        return $this->render('account/profil/profil_infos.html.twig', [
            'form' => $form->createView(),
            'isPro' => $isPro,
            'isClient' => $isClient,
        ]);
    }

    #[Route('/my-account/reservations', name: 'app_profil_reservations')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function mesReservations(Security $security, ReservationRepository $reservationRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();
    
        if (!$user || !$user->getClient()) {
            $this->addFlash('error', 'Vous devez être connecté en tant que client pour voir vos réservations.');
            return $this->redirectToRoute('app_login');
        }
    
        $client = $user->getClient();
        $reservations = $reservationRepo->findBy(['client' => $client]);
    
        return $this->render('account/profil/profil_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/my-account/settings', name: 'app_profil_settings')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            // Vérifie que l'ancien mot de passe est correct
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new \Symfony\Component\Form\FormError('Mot de passe actuel incorrect.'));
            } else {
                // Encode et enregistre le nouveau mot de passe
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');
                return $this->redirectToRoute('app_profil_settings');
            }
        }

        return $this->render('account/profil/profil_settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/my-account/delete', name: 'app_delete_account', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, Security $security): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('delete-account', $request->request->get('_token'))) {

            $username = $user->getEmail();

            // Supprime les entités liées si besoin
            if ($user->getClient()) {
                $entityManager->remove($user->getClient());
            }
            if ($user->getProfessional()) {
                $entityManager->remove($user->getProfessional());
            }

            $entityManager->remove($user);
            $entityManager->flush();

            // Déconnexion propre : on vide le token
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            $this->addFlash('success', "Le compte {$username} a été supprimé.");
        }

        // Déconnexion manuelle
        return $this->redirectToRoute('app_home');
    }
}
