<?= "<?php\n" ?>

namespace App\Service;

use <?= $interfaceNamespace ?>;
use <?= $repositoryClass ?>;
use <?= $entityClass ?>;
use <?= $entityClass ?>Collection;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class <?= $name ?> implements <?= $interface ?>
{
    private <?= $repositoryShortName ?> $repository;

    public function __construct(<?= $repositoryShortName ?> $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crée de nouvelles entités {{ entityClass|basename }} à partir d'une collection.
     *
     * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection
     * @return array{created: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection, existing: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection}
     */
    public function create<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection(<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection): array
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


    /**
    * Met à jour une collection d'entités <?= basename(str_replace('\\', '/', $entityClass)) ?>.
    *
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection
    * @return array{updated: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection, not_found: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection}
    */
    public function update<?= basename(str_replace('\\', '/', $entityClass)) ?>s(<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection): array
    {
        $toUpdate = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();
        $notFound = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();

        $em = $this->repository->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Récupération dynamique des propriétés simples via Reflection
        $columns = [];
        $reflection = new \ReflectionClass($this->repository->getEntityClass());
        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(\Doctrine\ORM\Mapping\Column::class);
            if (!empty($attrs)) {
                $columns[] = $property->getName();
            }
        }

        foreach ($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection->get<?= basename(str_replace('\\', '/', $entityClass)) ?>s() as $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>) {
            $id = $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>->getId();

            if ($id === null) {
                $notFound->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
                continue;
            }

            $existing = $this->repository->find($id);
            if (!$existing) {
                $notFound->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
                continue;
            }

            // Snapshot original Doctrine (avant modification)
            $orig = $uow->getOriginalEntityData($existing);

            $hasChanged = false;
            foreach ($columns as $column) {
                $getter = 'get' . ucfirst($column);
                $setter = 'set' . ucfirst($column);

                $newValue = $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>->$getter();
                $oldValue = $orig[$column] ?? $existing->$getter();

                if ($oldValue !== $newValue) {
                    $existing->$setter($newValue);
                    $hasChanged = true;
                }
            }

            if ($hasChanged) {
                $toUpdate->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($existing);
            }
        }

        $this->repository->update<?= basename(str_replace('\\', '/', $entityClass)) ?>s($toUpdate);

        return [
            'updated'   => $toUpdate,
            'not_found' => $notFound,
        ];
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

    public function findAll(): array
    {
        return $this->repository->findAll();
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
