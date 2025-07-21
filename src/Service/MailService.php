<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }
    /**
     * Envoie des différents mails au client
     */
    /**
     * Envoie un e-mail au client dès qu’il effectue une réservation.
     */
    public function sendReservationCreated(
        string $serviceName,
        string $clientName,
        string $to,
        string $compagnyName,
        string $cityCompagny,
        \DateTimeInterface $date,
        string $hour
    ): void {
        $subject = 'Votre réservation a bien été prise en compte';

        $body = $this->twig->render('emails/reservation_created.html.twig', [
            'serviceName' => $serviceName,
            'clientName' => $clientName,
            'compagnyName' => $compagnyName,
            'cityCompagny' => $cityCompagny,
            'date' => $date->format('d/m/Y'),
            'hour' => $hour,
        ]);

        $email = (new Email())
            ->from('no-reply@flashservice.fr')
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }

    /**
     * Envoie un e-mail au client lorsque la réservation est confirmée par le professionnel.
     */
    public function sendReservationConfirmed(
        string $serviceName,
        string $clientName,
        string $to,
        string $compagnyName,
        string $cityCompagny,
        \DateTimeInterface $date,
        string $hour,
        string $price
    ): void {
        $subject = 'Votre réservation a été confirmée !';

        $body = $this->twig->render('emails/reservation_confirmed.html.twig', [
            'serviceName' => $serviceName,
            'clientName' => $clientName,
            'companyName' => $compagnyName,
            'cityCompany' => $cityCompagny,
            'date' => $date->format('d/m/Y'),
            'hour' => $hour,
            'price' => $price
        ]);

        $email = (new Email())
            ->from('no-reply@flashservice.fr')
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
    public function sendReservationCancelled(string $serviceName, string $clientName, string $to, string $compagnyName, string $cityCompagny, \DateTimeInterface $date, string $hour): void
    {
        $subject = 'Réservation annulée - FlashService';

        $body = $this->twig->render('emails/reservation_cancelled.html.twig', [
            'serviceName' => $serviceName,
            'clientName' => $clientName,
            'compagnyName' => $compagnyName,
            'cityCompagny' => $cityCompagny,
            'date' => $date->format('d/m/Y'),
            'hour' => $hour,
        ]);

        $email = (new Email())
            ->from('no-reply@flashservice.fr')
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
    public function sendReservationUpdated(string $serviceName, string $clientName, string $to, string $compagnyName, string $cityCompagny, \DateTimeInterface $date, string $hour): void
    {
        $subject = 'Réservation modifiée - FlashService';

        $body = $this->twig->render('emails/reservation_updated.html.twig', [
            'serviceName' => $serviceName,
            'clientName' => $clientName,
            'compagnyName' => $compagnyName,
            'cityCompagny' => $cityCompagny,
            'date' => $date->format('d/m/Y'),
            'hour' => $hour,
        ]);

        $email = (new Email())
            ->from('no-reply@flashservice.fr')
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
    /**
     * Envoie des différents mails au professionel
     */
    public function sendReservationCancelledToProfessional(string $serviceName, string $to, string $clientName, \DateTimeInterface $date, string $hour): void
    {
        $subject = 'Annulation de réservation - FlashService';

        $body = $this->twig->render('emails/pro_reservation_cancelled.html.twig', [
            'serviceName' => $serviceName,
            'clientName' => $clientName,
            'date' => $date->format('d/m/Y'),
            'hour' => $hour,
        ]);

        $email = (new Email())
            ->from('no-reply@flashservice.fr')
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }

    public function sendReservationUpdatedToProfessional(string $serviceName, string $to, string $clientName, \DateTimeInterface $date, string $hour): void
    {
        $subject = 'Modification de réservation - FlashService';

        $body = $this->twig->render('emails/pro_reservation_updated.html.twig', [
            'serviceName' => $serviceName,
            'clientName' => $clientName,
            'date' => $date->format('d/m/Y'),
            'hour' => $hour,
        ]);

        $email = (new Email())
            ->from('no-reply@flashservice.fr')
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
    public function sendReservationToConfirmToProfessional(string $serviceName, string $to, string $clientName, \DateTimeInterface $date, string $hour): void
    {
        $subject = 'Nouvelle réservation à confirmer - FlashService';

        $body = $this->twig->render('emails/pro_reservation_to_confirm.html.twig', [
            'serviceName' => $serviceName,
            'clientName' => $clientName,
            'date' => $date->format('d/m/Y'),
            'hour' => $hour,
        ]);

        $email = (new Email())
            ->from('no-reply@flashservice.fr')
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
  

public function sendReservationUpdatedToClient(string $serviceName, string $clientName, string $to, string $serviceTitle, \DateTimeInterface $date, string $hour): void
{
    $subject = 'Confirmation de votre réservation - FlashServices';
    $body = $this->twig->render('emails/reservation_updated.html.twig', [
        'serviceName' => $serviceName,
        'clientName' => $clientName,
        'serviceTitle' => $serviceTitle,
        'date' => $date->format('d/m/Y'),
        'hour' => $hour,
    ]);
    $email = (new Email())
        ->from('no-reply@flashservice.fr')
        ->to($to)
        ->subject($subject)
        ->html($body);

    $this->mailer->send($email);
}

public function sendReservationCancelledToClient(
    string $serviceName,
    string $clientName,
    string $to,
    string $serviceTitle,
    string $compagnyName,
    string $cityCompagny, 
    \DateTimeInterface $date,
    string $hour
): void {
    $subject = 'Votre réservation a été annulée - FlashService';
    $body = $this->twig->render('emails/reservation_cancelled.html.twig', [
        'serviceName' => $serviceName,
        'clientName' => $clientName,
        'serviceTitle' => $serviceTitle,
        'compagnyName' => $compagnyName,
        'cityCompagny' => $cityCompagny, 
        'date' => $date->format('d/m/Y'),
        'hour' => $hour,
    ]);
    $email = (new Email())
        ->from('no-reply@flashservice.fr')
        ->to($to)
        ->subject($subject)
        ->html($body);

    $this->mailer->send($email);
}
}
