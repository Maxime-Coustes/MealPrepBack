<?= "<?php\n" ?>

namespace App\Service;

use <?= $interfaceNamespace ?>;
use <?= $repositoryClass ?>;
use <?= $entityClass ?>;
use <?= $entityClass ?>Collection;
use Src\Utils\DoctrineHelper;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class <?= $name ?> implements <?= $interface ?>
{
    private <?= $repositoryShortName ?> $repository;

    public function __construct(<?= $repositoryShortName ?> $repository)
    {
        $this->repository = $repository;
    }

    /**
    * Crée de nouvelles entités <?= basename(str_replace('\\', '/', $entityClass)) ?> à partir d'une collection.
    *
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection
    * @return array{created: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection, existing: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection}
    */
    public function create<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection(<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection): array
    {
        $columns = DoctrineHelper::getDoctrineColumns($this->repository->getEntityClass());

        $new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();
        $existing = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();

        foreach ($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection->get<?= basename(str_replace('\\', '/', $entityClass)) ?>s() as $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>) {
            // Appliquer les règles génériques (ex: normalisation du nom)
            $this->applyGenericRules($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>, $columns);

            if ($this->checkIfExists($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>)) {
                $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?> = $this->repository->findOneBy([
                    'name' => $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>->getName()
                ]);
                $existing->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
            } else {
                $new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
            }
        }

        if (!$new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection->isEmpty()) {
            $this->repository->create<?= basename(str_replace('\\', '/', $entityClass)) ?>s($new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection);
        }

        return [
            'created'  => $new<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection,
            'existing' => $existing,
        ];
    }

    /**
    * Applique des règles de normalisation sur les propriétés de l'entité <?= basename(str_replace('\\', '/', $entityClass)) ?>
    * en fonction des colonnes connues de Doctrine.
    *
    * Exemple actuel :
    * - "name" : force la casse à "Majuscule + minuscules".
    *
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>
    * @param string[] $columns
    */
    private function applyGenericRules(<?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>, array $columns): void
    {
        foreach ($columns as $column) {
            $getter = 'get' . ucfirst($column);
            $setter = 'set' . ucfirst($column);

            if (!method_exists($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>, $getter) || !method_exists($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>, $setter)) {
                continue;
            }

            $value = $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>->$getter();

            if ($column === 'name' && $value !== null) {
                $value = ucfirst(strtolower($value));
            }

            $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>->$setter($value);
        }
    }



    /**
    * Met à jour une collection d'entités <?= basename(str_replace('\\', '/', $entityClass)) ?> en appliquant les nouvelles valeurs fournies.
    *
    * Chaque entité de la collection est comparée à l'entité existante en base.
    * Si l'entité existe, les valeurs de ses propriétés sont mises à jour dynamiquement
    * via applyNewValues / setFieldIfExists. Les entités mises à jour sont renvoyées,
    * ainsi que celles non trouvées en base.
    *
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection
    *
    * @return array{
    *     updated: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection,   // Collection des entités mises à jour
    *     not_found: <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection  // Collection des entités non trouvées en base
    * }
    */
    public function update<?= basename(str_replace('\\', '/', $entityClass)) ?>s(<?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection): array
    {
        $toUpdate = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();
        $notFound = new <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection();

        foreach ($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>Collection->get<?= basename(str_replace('\\', '/', $entityClass)) ?>s() as $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>) {
            $existing = $this->repository->find($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>->getId());
            if (!$existing) {
                $notFound->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>);
                continue;
            }

            $this->applyNewValues($existing, $<?= lcfirst(basename(str_replace('\\', '/', $entityClass))) ?>, $toUpdate);
        }

        $this->repository->update<?= basename(str_replace('\\', '/', $entityClass)) ?>s($toUpdate);

        return [
            'updated'   => $toUpdate,
            'not_found' => $notFound,
        ];
    }

    /**
    * Applique les nouvelles valeurs d'un payload sur l'entité existante.
    *
    * Parcourt toutes les clés du tableau __newValues (placeholder temporaire) du payload
    * et appelle setFieldIfExists pour chaque champ.
    *
    * @param object $existing L'entité existante en base
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?> $payload L'entité contenant les nouvelles valeurs
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $toUpdate La collection où ajouter les entités modifiées
    */
    private function applyNewValues($existing, $payload, <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $toUpdate): void
    {
        $newValues = $payload->__newValues ?? [];

        foreach ($newValues as $field => $value) {
            $this->setFieldIfExists($existing, $field, $value, $toUpdate);
        }
    }

    /**
    * Met à jour une propriété d'une entité si celle-ci existe et que sa valeur est différente.
    *
    * Vérifie l'existence de la propriété, du getter et du setter correspondants avant
    * de modifier la valeur. Ajoute l'entité à la collection des entités mises à jour si nécessaire.
    *
    * @param object $entity L'entité à modifier
    * @param string $field Le nom du champ à mettre à jour
    * @param mixed $newValue La nouvelle valeur à appliquer
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $toUpdate La collection où ajouter l'entité si modifiée
    */
    private function setFieldIfExists($entity, string $field, $newValue, <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection $toUpdate): void
    {
        if (!property_exists($entity, $field)) return;

        $getter = 'get' . ucfirst($field);
        $setter = 'set' . ucfirst($field);

        if (!method_exists($entity, $getter) || !method_exists($entity, $setter)) return;

        $oldValue = $entity->$getter();
        if ($oldValue !== $newValue) {
            $entity->$setter($newValue);
            $toUpdate->add<?= basename(str_replace('\\', '/', $entityClass)) ?>($entity);
        }
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
            throw new NotFoundHttpException(sprintf(' <?= basename(str_replace('\\', '/', $entityClass)) ?> with id %d not found.', $id));
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
