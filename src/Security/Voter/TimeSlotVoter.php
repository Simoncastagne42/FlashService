<?php

namespace App\Security\Voter;

use App\Entity\TimeSlot;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class TimeSlotVoter extends Voter
{
    public const VIEW = 'TIMESLOT_VIEW';
    public const EDIT = 'TIMESLOT_EDIT';
    public const DELETE = 'TIMESLOT_DELETE';


    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof TimeSlot;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var TimeSlot $timeSlot */
        $timeSlot = $subject;

        $professional = $user->getProfessional();

        if (!$professional) {
            return false;
        }

        return $timeSlot->getProfessional() === $professional;
    }
}