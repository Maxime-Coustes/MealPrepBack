<?php
namespace App\Repository;

interface SolidRepositoryInterface
{
    /**
     * @return class-string
     */
    public function getEntityClass(): string;
}
