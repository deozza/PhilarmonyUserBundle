<?php

namespace Deozza\PhilarmonyUserBundle\Repository;

use Deozza\PhilarmonyUserBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

abstract class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry, $class)
    {
        parent::__construct($registry, $class);
    }
    public function findByUsernameOrEmail($username, $email)
    {
        $parameters = [
            'username' => $username,
            'email' => $email
        ];
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder
            ->select('u')
            ->where('u.username = :username')
            ->orWhere('u.email = :email')
            ->setParameters($parameters);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    public function findAllFiltered(Array $filters)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->select('u');

        if(!empty($filters))
        {
            $parameters = [];
            foreach ($filters as $filter=>$value)
            {
                if(gettype($value) == "string")
                {
                    $queryBuilder->andWhere('u.'.$filter.' LIKE :'.$filter);
                    $parameters[$filter] = "%".$value."%";
                }
                elseif(gettype($value) == "int")
                {
                    $queryBuilder->andWhere('u.'.$filter.' = :'.$filter);
                }
            }

            $queryBuilder->setParameters($parameters);
        }

        return $queryBuilder->getQuery();
    }

}
