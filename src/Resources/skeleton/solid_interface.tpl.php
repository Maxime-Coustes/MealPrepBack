<?= "<?php\n" ?>

namespace App\Interface;

use App\Entity\<?= $entityName ?>;

interface <?= $interfaceName ?>
{
    public function create(<?= $entityName ?> $<?= lcfirst($entityName) ?>): void;

    public function update(<?= $entityName ?> $<?= lcfirst($entityName) ?>): void;

    public function delete(<?= $entityName ?> $<?= lcfirst($entityName) ?>): void;

    public function find(int $id): ?<?= $entityName ?>;
}
