<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Service;
use App\Entity\User;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\TimeSlotRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ReservationController extends AbstractController
{
    #[Route('/services/{id}/reserver', name: 'service_reserver')]
    public function reserver(
        Request $request,
        Service $service,
        EntityManagerInterface $em,
        Security $security,
        ReservationRepository $reservationRepo,
        TimeSlotRepository $timeSlotRepo,
        MailService $mailService
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour réserver.');
            return $this->redirectToRoute('app_login');
        }

        if (!$user->getClient()) {
            $this->addFlash('error', 'Vous devez compléter votre profil client.');
            return $this->redirectToRoute('app_profil_infos');
        }

        if (!in_array('ROLE_CLIENT', $user->getRoles())) {
            $this->addFlash('error', 'Seuls les clients peuvent réserver.');
            return $this->redirectToRoute('app_home');
        }

        $reservation = new Reservation();
        $reservation->setService($service);
        $reservation->setClient($user->getClient());

        $reservedSlotIds = $reservationRepo->findReservedSlotIdsForService($service->getId());
        $availableSlots = $timeSlotRepo->findAvailableSlotsForService($service->getId(), $reservedSlotIds);

        $form = $this->createForm(ReservationType::class, $reservation, [
            'available_slots' => $availableSlots,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setStatut(Reservation::STATUS_PENDING);
            $em->persist($reservation);
            $em->flush();

            // Mail au client
            $mailService->sendReservationCreated(
                $reservation->getClient()->getFullName(),
                $reservation->getClient()->getUser()->getEmail(),
                $service->getProfessional()->getCompagnyName(),
                $service->getProfessional()->getCityCompagny(),
                $reservation->getTimeSlot()->getDate(),
                $reservation->getHeureDebut()?->format('H:i')
            );

            // Mail au professionnel pour confirmation
            $mailService->sendReservationToConfirmToProfessional(
                $service->getProfessional()->getUser()->getEmail(),
                $reservation->getClient()->getFullName(),
                $reservation->getTimeSlot()->getDate(),
                $reservation->getHeureDebut()?->format('H:i')
            );

            $this->addFlash('success', 'Réservation envoyée !');
            return $this->redirectToRoute('app_profil_reservations');
        }

        return $this->render('reservation/reservation.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }

    #[Route('/reservation/{id}/modifier', name: 'reservation_modifier')]
    public function modifier(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em,
        Security $security,
        TimeSlotRepository $timeSlotRepo,
        ReservationRepository $reservationRepo,
        MailService $mailService
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user || $reservation->getClient() !== $user->getClient()) {
            throw $this->createAccessDeniedException();
        }

        if (in_array($reservation->getStatut(), [Reservation::STATUS_CONFIRMED, Reservation::STATUS_CANCELLED])) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette réservation.');
            return $this->redirectToRoute('app_profil_reservations');
        }

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
            $em->flush();
            $mailService->sendReservationUpdated(
                $reservation->getClient()->getFullName(),
                $reservation->getClient()->getUser()->getEmail(),
                $reservation->getService()->getProfessional()->getCompagnyName(),
                $reservation->getService()->getProfessional()->getCityCompagny(),
                $reservation->getTimeSlot()->getDate(),
                $reservation->getTimeSlot()->getHeureDebut()->format('H:i')
            );
            $mailService->sendReservationUpdatedToProfessional(
                $reservation->getService()->getProfessional()->getUser()->getEmail(),
                $reservation->getClient()->getFullName(),
                $reservation->getTimeSlot()->getDate(),
                $reservation->getTimeSlot()->getHeureDebut()?->format('H:i')
            );


            $this->addFlash('success', 'Votre réservation a bien été modifiée.');
            return $this->redirectToRoute('app_profil_reservations');
        }

        return $this->render('account/profil/profil_modifier_reservations.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation
        ]);
    }

    #[Route('/reservation/{id}/annuler', name: 'reservation_annuler')]
    public function annulerReservation(
        Reservation $reservation,
        Security $security,
        EntityManagerInterface $em,
        MailService $mailService
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user || $reservation->getClient() !== $user->getClient()) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_profil_reservations');
        }

        if (in_array($reservation->getStatut(), [Reservation::STATUS_CONFIRMED, Reservation::STATUS_CANCELLED])) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler cette réservation.');
            return $this->redirectToRoute('app_profil_reservations');
        }

        $reservation->setStatut(Reservation::STATUS_CANCELLED);
        $em->flush();

        // Mail au client
        $mailService->sendReservationCancelledToClient(
            $reservation->getClient()->getFullName(),
            $reservation->getClient()->getUser()->getEmail(),
            $reservation->getService()->getName(),
            $reservation->getService()->getProfessional()->getCompagnyName(),
            $reservation->getService()->getProfessional()->getCityCompagny(),
            $reservation->getTimeSlot()->getDate(),
            $reservation->getHeureDebut()?->format('H:i')
        );

        // Mail au professionnel
        $mailService->sendReservationCancelledToProfessional(
            $reservation->getService()->getProfessional()->getUser()->getEmail(),
            $reservation->getClient()->getFullName(),
            $reservation->getTimeSlot()->getDate(),
            $reservation->getHeureDebut()?->format('H:i')
        );

        $this->addFlash('success', 'Réservation annulée avec succès.');
        return $this->redirectToRoute('app_profil_reservations');
    }

    #[Route('/my-account/reservations', name: 'app_profil_reservations')]
    public function profilReservations(
        ReservationRepository $reservationRepository,
        Security $security
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        $reservations = $reservationRepository->findBy(
            ['client' => $user->getClient()],
            ['createdAt' => 'DESC'] 
        );

        return $this->render('account/profil/profil_reservations.html.twig', [
            'reservations' => $reservations
        ]);
    }
}
