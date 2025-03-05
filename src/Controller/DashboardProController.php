<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardProController extends AbstractController
{
    #[Route('/dashboard/pro', name: 'dashboard_pro')]
    public function index(): Response
    {
        return $this->render('dashboard_pro/index.html.twig');
    }
}
