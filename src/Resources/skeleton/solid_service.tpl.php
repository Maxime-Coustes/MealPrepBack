<?= "<?php\n" ?>

namespace App\Service;

use <?= $interfaceNamespace ?>;
use <?= $repositoryClass ?>;
use <?= $entityClass ?>;

class <?= $name ?> implements <?= $interface ?>
{
    private <?= $repositoryShortName ?> $repository;

    public function __construct(<?= $repositoryShortName ?> $repository)
    {
        $this->repository = $repository;
    }

    // Implémentation des méthodes
}
