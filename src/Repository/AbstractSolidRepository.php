<?php 
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractSolidRepository extends ServiceEntityRepository implements SolidRepositoryInterface
{
    public function getEntityClass(): string
    {
        return $this->getClassName(); // méthode native de ServiceEntityRepository
    }
}
