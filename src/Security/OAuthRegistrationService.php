<?php 

namespace App\Security;

Use App\Entity\User;
Use App\Repository\UserRepository;
Use League\OAuth2\Client\Provider\GoogleUser;
Use League\OAuth2\Client\Provider\ResourceOwnerInterface;


final readonly class OAuthRegistrationService
{

    public function __construct(
        private UserRepository $repository
    )
    {
        
    }

    /**
     * @param GoogleUser $resourceOwner
     */


    public function persist(ResourceOwnerInterface $resourceOwner): User
    {
        $user = match (true) {
            $resourceOwner instanceof GoogleUser => (new User())
                ->setEmail($resourceOwner->getEmail())
                ->setGoogleId($resourceOwner->getId())
                ->setIsVerified(true),

        };
        
        $this->repository->add($user, flush: true);
        return $user;

    }













}