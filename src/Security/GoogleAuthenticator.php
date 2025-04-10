<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;




class GoogleAuthenticator extends AbstractOAuthAuthenticator

{
    protected string $serviceName = 'google';

    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $repository): ?User
    {
        if (!($resourceOwner instanceof GoogleUser)) {
            throw new \RuntimeException("Expecting google user");
        }
        if (true !== ($resourceOwner->toArray()['email_verified'] ?? null)) {

            throw new AuthenticationException("Email not verified");
        }
        
        return $repository->findOneBy([
            'google_id' => $resourceOwner->getId(),
            'email' => $resourceOwner->getEmail()
        ]);
    }
    
}
