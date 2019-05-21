<?php
namespace Deozza\PhilarmonyUserBundle\Security;

use Deozza\PhilarmonyUserBundle\Entity\User;
use Deozza\PhilarmonyUserBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    protected $tokenValueRepository;
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAuthToken($authTokenHeader)
    {
        return $this->tokenValueRepository->findOneByValue($authTokenHeader);
    }

    public function loadUserByUsername($username)
    {
        return $this->userRepository->findByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}