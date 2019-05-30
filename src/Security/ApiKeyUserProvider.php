<?php
namespace Deozza\PhilarmonyUserBundle\Security;

use Deozza\PhilarmonyUserBundle\Repository\ApiTokenRepository;
use Deozza\PhilarmonyUserBundle\Service\UserSchemaLoader;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    public function __construct(UserSchemaLoader $userSchemaLoader, ApiTokenRepository $apiTokenRepository)
    {
        $this->userEntity = $userSchemaLoader->loadUserEntityClass();
        $this->userRepository = $userSchemaLoader->loadUserRepositoryClass();
        $this->apiTokenRepository = $apiTokenRepository;
    }

    public function getAuthToken($authTokenHeader)
    {
        return $this->apiTokenRepository->findOneByValue($authTokenHeader);
    }

    public function loadUserByUsername($username)
    {
        $repository = new $this->userRepository;
        return $repository->findByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        $entity = new $this->userEntity;
        return $entity === $class;
    }
}