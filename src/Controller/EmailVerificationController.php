<?php

namespace App\Controller;

// src/Controller/EmailVerificationController.php

use App\Entity\User;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailVerificationController extends AbstractController
{
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, EntityManagerInterface $em): Response
    {
        $id = $request->get('id');

        if (!$id) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Vérifier la validité du lien
        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());

            return $this->redirectToRoute('app_register');
        }

        // Valider l'utilisateur
        $user->setIsVerified(true);
        $em->flush();

        $this->addFlash('success', 'Votre email a bien été confirmé. Vous pouvez maintenant vous connecter.');
        // 🧼 Invalider la session et supprimer le cookie REMEMBERME
        $request->getSession()->invalidate();

        $response = $this->redirectToRoute('app_login');
        $response->headers->clearCookie('REMEMBERME');

        return $this->redirectToRoute('app_login');
    }
}
