<?php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @template T of object
 * @extends ServiceEntityRepository<T>
 */
abstract class AbstractSolidRepository extends ServiceEntityRepository implements SolidRepositoryInterface
{
    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->getClassName(); // méthode native de ServiceEntityRepository
    }
}
