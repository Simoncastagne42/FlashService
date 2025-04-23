<?php

namespace App\Security\Voter;

use App\Entity\Service;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class ServiceVoter extends Voter
{
    public const VIEW = 'RESERVATION_VIEW';
    public const EDIT = 'RESERVATION_EDIT';
    public const DELETE = 'RESERVATION_DELETE';


    
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Service;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User || !$user->getProfessional()) {
            return false;
        }

        /** @var Service $service */
        $service = $subject;

        return $service->getProfessional() === $user->getProfessional();
    }
}

