<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            error_log('User non vérifié : ' . $user->getEmail());
            throw new CustomUserMessageAccountStatusException(
                'Veuillez confirmer votre adresse email avant de vous connecter.'
            );
        } else {
            error_log('User vérifié : ' . $user->getEmail());
        }
    }
    public function checkPostAuth(UserInterface $user): void {}
}
