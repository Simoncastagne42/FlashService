<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\GoogleAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    public const SCOPES = [
        'google' => [],
    ];


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/oauth/connect/{service}', name: 'auth_oauth_connect')]
    public function connect(string $service, ClientRegistry $clientRegistry): RedirectResponse
    {
        if (!in_array($service, array_keys(self::SCOPES), true)) {
            throw $this->createNotFoundException();
        }

        return $clientRegistry
            ->getClient($service)
            ->redirect(self::SCOPES[$service], []);
    }
    #[Route('/oauth/check/{service}', name: 'auth_oauth_check')]
    public function check(): Response
    {
        return new Response(status: 200);
    }


    #[Route('/choose-role', name: 'app_choose_role')]
    public function chooseRole(Request $request, Security $security, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator, GoogleAuthenticator $authenticator): Response
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $role = $request->request->get('role');

            if (in_array($role, ['ROLE_CLIENT', 'ROLE_PROFESSIONNEL'])) {
                $user->setRoles([$role]);
                $entityManager->flush();

                $userAuthenticator->authenticateUser($user, $authenticator, $request);

               return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('security/choose_role.html.twig');
    }
}
