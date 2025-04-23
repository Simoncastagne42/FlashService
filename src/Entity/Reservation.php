<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   
    public const STATUS_PENDING = 'en_attente';
    public const STATUS_CONFIRMED = 'confirmée';
    public const STATUS_CANCELLED = 'annulée';
    
    #[ORM\Column(length: 255, type: 'string')]
    private ?string $statut = self::STATUS_PENDING;


    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Service $service = null;

    #[ORM\OneToOne(inversedBy: 'reservation', cascade: ['persist', 'remove'])]
    private ?TimeSlot $timeSlot = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public static function getAllowedStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
        ];
    }


    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getTimeSlot(): ?TimeSlot
    {
        return $this->timeSlot;
    }

    public function setTimeSlot(?TimeSlot $timeSlot): static
    {
        $this->timeSlot = $timeSlot;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->getTimeSlot()?->getDate();
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->getTimeSlot()?->getHeureDebut();
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->getTimeSlot()?->getHeureFin();
    }

    public function __toString(): string
    {
        return 'Réservation #' . $this->id ?? 'Réservation';
    }
}
