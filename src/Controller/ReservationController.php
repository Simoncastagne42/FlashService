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
use Symfony\Component\Security\Http\Attribute\IsGranted;



final class ReservationController extends AbstractController
{
    #[Route('/services/{id}/reserver', name: 'service_reserver')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function reserver(
        Request $request,
        Service $service,
        EntityManagerInterface $em,
        Security $security,
        TimeSlotRepository $timeSlotRepository,
        MailService $mailService
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();
        // Si ce n’est pas un client
        if (!in_array('ROLE_CLIENT', $user->getRoles())) {
            $this->addFlash('error', 'Vous devez avoir un compte de type "Client" pour réserver.');
            return $this->redirectToRoute('app_home');
        }
        // Si le profil client n’est pas encore rempli
        if (!$user->getClient()) {
            $this->addFlash('error', 'Merci de compléter votre profil pour effectuer une réservation.');

            // stock l’URL de retour
            $request->getSession()->set('redirect_after_profile', $request->getUri());

            return $this->redirectToRoute('app_profil_infos');
        }
        // Récupérer les créneaux disponibles pour ce service
        $availableSlots = $timeSlotRepository->findAvailableSlotsForService($service->getId());

        if (empty($availableSlots)) {
            $this->addFlash('warning', 'Aucun créneau disponible pour ce service actuellement.');
            return $this->redirectToRoute('app_home');
        }

        $reservation = new Reservation();
        $reservation->setService($service);
        $reservation->setClient($user->getClient());

        $form = $this->createForm(ReservationType::class, $reservation, [
            'available_slots' => $availableSlots
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $selectedTimeSlot = $reservation->getTimeSlot();

            if (!$timeSlotRepository->isAvailable($selectedTimeSlot)) {
                $this->addFlash('error', 'Ce créneau est déjà réservé. Veuillez en choisir un autre.');
                return $this->redirectToRoute('service_reserver', ['id' => $service->getId()]);
            }

            $reservation->setStatut(Reservation::STATUS_PENDING);
            $em->persist($reservation);
            $em->flush();

            // Envoi des mails
            $mailService->sendReservationCreated(
                $reservation->getService()->getName(),
                $reservation->getClient()->getFullName(),
                $reservation->getClient()->getUser()->getEmail(),
                $service->getProfessional()->getCompagnyName(),
                $service->getProfessional()->getCityCompagny(),
                $reservation->getTimeSlot()->getDate(),
                $reservation->getHeureDebut()?->format('H:i')
            );

            $mailService->sendReservationToConfirmToProfessional(
                $reservation->getService()->getName(),
                $service->getProfessional()->getUser()->getEmail(),
                $reservation->getClient()->getFullName(),
                $reservation->getTimeSlot()->getDate(),
                $reservation->getHeureDebut()?->format('H:i')
            );

            $this->addFlash('success', 'Réservation envoyée avec succès !');
            return $this->redirectToRoute('app_profil_reservations');
        }

        return $this->render('reservation/reservation.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }


    #[Route('/reservation/{id}/modifier', name: 'reservation_modifier')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function modifier(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em,
        Security $security,
        TimeSlotRepository $timeSlotRepo,
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

        $form = $this->createForm(ReservationType::class, $reservation, [
            'available_slots' => $timeSlotRepo->findAvailableSlotsForService(
                $reservation->getService()->getId()
            ),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $mailService->sendReservationUpdated(
                $reservation->getService()->getName(),
                $reservation->getClient()->getFullName(),
                $reservation->getClient()->getUser()->getEmail(),
                $reservation->getService()->getProfessional()->getCompagnyName(),
                $reservation->getService()->getProfessional()->getCityCompagny(),
                $reservation->getTimeSlot()->getDate(),
                $reservation->getTimeSlot()->getHeureDebut()->format('H:i')
            );
            $mailService->sendReservationUpdatedToProfessional(
                $reservation->getService()->getName(),
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
        // Changer juste le statut, garder le timeSlot lié
        $reservation->setStatut(Reservation::STATUS_CANCELLED);
        // Envoi des mails
        $mailService->sendReservationCancelledToClient(
            $reservation->getService()->getName(),
            $reservation->getClient()->getFullName(),
            $reservation->getClient()->getUser()->getEmail(),
            $reservation->getService()->getName(),
            $reservation->getService()->getProfessional()->getCompagnyName(),
            $reservation->getService()->getProfessional()->getCityCompagny(),
            $reservation->getTimeSlot()->getDate(),
            $reservation->getHeureDebut()?->format('H:i')
        );

        $mailService->sendReservationCancelledToProfessional(
            $reservation->getService()->getName(),
            $reservation->getService()->getProfessional()->getUser()->getEmail(),
            $reservation->getClient()->getFullName(),
            $reservation->getTimeSlot()->getDate(),
            $reservation->getHeureDebut()?->format('H:i')
        );
        $em->flush();
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
