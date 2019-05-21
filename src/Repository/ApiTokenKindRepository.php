<?php

namespace Deozza\PhilarmonyUserBundle\Repository;

use Deozza\PhilarmonyUserBundle\Entity\ApiTokenKind;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ApiTokenKind|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiTokenKind|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiTokenKind[]    findAll()
 * @method ApiTokenKind[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiTokenKindRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ApiTokenKind::class);
    }
}
