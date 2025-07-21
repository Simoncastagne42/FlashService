<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_mentions')]
    public function mentions(): Response
    {
        return $this->render('legal/legal_mentions.html.twig');
    }

    #[Route('/politique-confidentialite', name: 'app_privacy_policy')]
    public function privacy(): Response
    {
        return $this->render('legal/privacy_policy.html.twig');
    }

    #[Route('/gestion-cookies', name: 'app_cookie_policy')]
    public function cookies(): Response
    {
        return $this->render('legal/cookie_policy.html.twig');
    }
}