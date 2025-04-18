<?php

namespace App\Security\Voter;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReservationVoter extends Voter
{
    public const VIEW = 'RESERVATION_VIEW';
    public const EDIT = 'RESERVATION_EDIT';
    public const DELETE = 'RESERVATION_DELETE';


    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Reservation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User || !$user->getProfessional()) {
            return false;
        }

        /** @var Reservation $reservation */
        $reservation = $subject;

        // Vérifie que le pro est bien propriétaire du service réservé
        return $reservation->getService()->getProfessional() === $user->getProfessional();
    }
}