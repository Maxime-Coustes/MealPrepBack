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

        /**
     * Crée de nouvelles entités {{ entityClass|basename }} à partir d'une collection.
     *
     * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection
     * @return array{created: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection, existing: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection}
     */
    public function create<?= basename(str_replace('\\', '/', $entityClass)) ?>s(<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection): array
    {
        $new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();
        $existing = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();

        foreach ($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection->get<?= basename(str_replace('\\', '/', $entityClass)) ?>s() as $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>) {
            if ($this->checkIfExists($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>)) {
                $existing->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
            } else {
                $new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
                $this->repository->create<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
            }
        }

        return [
            'created' => $new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection,
            'existing' => $existing,
        ];
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

    /**
     * Vérifie si une entité existe déjà en base en fonction d'un champ unique
     *
     * @param <?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>
      @return bool
     */
    private function checkIfExists(<?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>): bool
    {
        // Ici, on suppose que la propriété "name" est unique (adapter si besoin)
        $existing<?= basename(str_replace('\\', '/', $entityClass)) ?> = $this->repository->findOneBy([
            'name' => $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>->getName(),
        ]);

        return $existing<?= basename(str_replace('\\', '/', $entityClass)) ?> !== null;
    }


}
