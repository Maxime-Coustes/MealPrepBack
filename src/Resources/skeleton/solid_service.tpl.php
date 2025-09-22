<?= "<?php\n" ?>

namespace App\Service;

use <?= $interfaceNamespace ?>;
use <?= $repositoryClass ?>;
use <?= $entityClass ?>;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class <?= $name ?> implements <?= $interface ?>
{
    private <?= $repositoryShortName ?> $repository;

    public function __construct(<?= $repositoryShortName ?> $repository)
    {
        $this->repository = $repository;
    }

    // Exemple de méthodes avec l’entité
    public function create(<?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>): void
    {
        die;
    }

    public function update(<?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>): void
    {
        die;
    }

    /**
    * Supprime une entité <?= basename(str_replace('\\', '/', $entityClass)) ?> par son ID.
    *
    * @param int $id
    *
    * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si l'entité n'existe pas
    */
    public function delete<?= basename(str_replace('\\', '/', $entityClass)) ?>ById(int $id): void
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException(sprintf('%s with id %d not found.', $id, $entity));
        }
        $this->repository->delete<?= basename(str_replace('\\', '/', $entityClass)) ?>($entity);
    }

    public function find(int $id): ?<?= basename(str_replace('\\', '/', $entityClass)) ?>
    {
        return $this->repository->find($id);
    }
}
