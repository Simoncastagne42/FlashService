<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reservation;
use App\Entity\Service;
use App\Entity\User;
use App\Form\ReservationType;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\ReservationRepository;
use App\Repository\TimeSlotRepository;


final class ReservationController extends AbstractController
{
    #[Route('/services/{id}/reserver', name: 'service_reserver')]
    public function reserver(
        Request $request,
        Service $service,
        EntityManagerInterface $entityManager,
        Security $security,
        ReservationRepository $reservationRepo,
        TimeSlotRepository $timeSlotRepo
    ): Response {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour réserver.');
            return $this->redirectToRoute('app_login');
        }
        if (!$user->getClient()) {
            $this->addFlash('error', 'Vous devez compléter votre profil client avant de réserver.');
            return $this->redirectToRoute('app_profil_infos'); 
        }

        if (!in_array('ROLE_CLIENT', $user->getRoles())) {
            $this->addFlash('error', 'Vous devez avoir le rôle "client" pour effectuer une réservation.');
            return $this->redirectToRoute('app_home');
        }

        $reservation = new Reservation();
        $reservation->setService($service);
        $reservation->setClient($user->getClient());

        // Récupère les ID des créneaux déjà réservés
        $reservedSlotIds = $reservationRepo->findReservedSlotIdsForService($service->getId());

        // Récupère les créneaux disponibles liés au service
        $availableSlots = $timeSlotRepo->findAvailableSlotsForService($service->getId(), $reservedSlotIds);

        $form = $this->createForm(ReservationType::class, $reservation, [
            'available_slots' => $availableSlots,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setStatut('en_attente');
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation envoyée !');
            return $this->redirectToRoute('app_profil_reservations');
        }

        return $this->render('reservation/reservation.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }

    #[Route('/reservation/{id}/annuler', name: 'reservation_annuler')]
    public function annulerReservation(Reservation $reservation, Security $security, EntityManagerInterface $enityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        // Sécurité : ne permettre que si le client est bien celui de la réservation
        if (!$user || $reservation->getClient() !== $user->getClient()) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_profil_reservations');
        }

        $reservation->setStatut('annulée');
        $enityManager->flush();

        $this->addFlash('success', 'Réservation annulée avec succès.');
        return $this->redirectToRoute('app_profil_reservations');
    }

    #[Route('/reservation/{id}/modifier', name: 'reservation_modifier')]
    public function modifier(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, Security $security, TimeSlotRepository $timeSlotRepo, ReservationRepository $reservationRepo): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user || !$reservation || $reservation->getClient() !== $user->getClient()) {
            throw $this->createAccessDeniedException();
        }

        // Bloquer la modification si la réservation est confirmée ou annulée
        if (in_array($reservation->getStatut(), ['confirmée', 'annulée'])) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier une réservation confirmée ou annulée.');
            return $this->redirectToRoute('app_profil_reservations');
        }

    
        // Récupère les créneaux disponibles (hors celui de cette résa)
        $reservedSlotIds = $reservationRepo->findReservedSlotIdsForService(
            $reservation->getService()->getId(),
            $reservation->getId()
        );

        $form = $this->createForm(ReservationType::class, $reservation, [
            'available_slots' => $timeSlotRepo->findAvailableSlotsForService(
                $reservation->getService()->getId(),
                $reservedSlotIds
            ),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre réservation a bien été modifiée.');
            return $this->redirectToRoute('app_profil_reservations');
        }

        return $this->render('account/profil/profil_modifier_reservations.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation
        ]);
    }
}
