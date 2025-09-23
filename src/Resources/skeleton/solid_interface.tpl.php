<?= "<?php\n" ?>

namespace App\Interface;

use App\Entity\<?= $entityName ?>;
use App\Entity\<?= $entityName ?>Collection;

interface <?= $interfaceName ?>
{
/**
     * Crée de nouvelles entités <?= basename(str_replace('\\', '/', $entityName)) ?> à partir d'une collection.
     *
     * @param <?= basename(str_replace('\\', '/', $entityName)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityName))) ?>Collection
     * @return array{created: <?= $entityName ?>Collection ?>, existing: <?= $entityName ?>Collection ?>}
     */
    public function create<?= $entityName ?>Collection(<?= basename(str_replace('\\', '/', $entityName)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityName))) ?>Collection): array;

    /**
    * Met à jour une collection d'entités <?= basename(str_replace('\\', '/', $entityName)) ?>.
    *
    * @param <?= basename(str_replace('\\', '/', $entityName)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityName))) ?>Collection
    * @return array{updated: <?= basename(str_replace('\\', '/', $entityName)) ?>Collection, not_found: <?= basename(str_replace('\\', '/', $entityName)) ?>Collection}
    */
    public function update<?= basename(str_replace('\\', '/', $entityName)) ?>s(<?= basename(str_replace('\\', '/', $entityName)) ?>Collection $<?= lcfirst(basename(str_replace('\\', '/', $entityName))) ?>Collection): array;


    /**
     * Supprime une entité <?= basename(str_replace('\\', '/', $entityName)) ?> par son ID.
     *
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si l'entité n'existe pas
     */
    public function delete<?= basename(str_replace('\\', '/', $entityName)) ?>ById(int $id): void;

    /**
     * Recherche une entité <?= basename(str_replace('\\', '/', $entityName)) ?> par son ID.
     *
     * @param int $id
     * @return <?= basename(str_replace('\\', '/', $entityName)) ?>|null
     */
    public function find(int $id): ?<?= basename(str_replace('\\', '/', $entityName)) ?>;
}
