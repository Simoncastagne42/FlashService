<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\TimeSlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    #[Route('/service/{id}', name: 'service_detail')]
    public function detail(Service $service, Request $request, EntityManagerInterface $em, TimeSlotRepository $timeSlotRepository): Response
    {
        // Création d'une réservation vide liée au service
        $reservation = new Reservation();
        $reservation->setService($service);

        // Formulaire de réservation
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        // Si soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'Vous devez être connecté pour réserver.');
                return $this->redirectToRoute('app_login');
            }

            $reservation->setClient($this->getUser()); 
            $em->persist($reservation);
            $em->flush();

            $this->addFlash('success', 'Réservation enregistrée avec succès !');
            return $this->redirectToRoute('dashboard_client'); // ou page de confirmation
        }

        // Créneaux liés au service
        $timeSlots = $timeSlotRepository->findBy(['service' => $service]);

        return $this->render('service/detail.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
            'timeSlots' => $timeSlots,
        ]);
    }
}
