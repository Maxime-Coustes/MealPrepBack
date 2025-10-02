<?= "<?php\n" ?>

namespace App\Entity;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, <?= basename(str_replace('\\', '/', $entityClass)) ?>>
 */
class <?= basename(str_replace('\\', '/', $entityClass)) ?>Collection implements Countable, IteratorAggregate
{
    /** @var <?= basename(str_replace('\\', '/', $entityClass)) ?>[] */
    private array $<?= lcfirst($entityName) ?>s = [];

    /**
     * @param <?= basename(str_replace('\\', '/', $entityClass)) ?>[] $<?= lcfirst($entityName) ?>s
     */
    public function __construct(array $<?= lcfirst($entityName) ?>s = [])
    {
        $this-><?= lcfirst($entityName) ?>s = $<?= lcfirst($entityName) ?>s;
    }

    /** @return <?= basename(str_replace('\\', '/', $entityClass)) ?>[] */
    public function get<?= $entityName ?>s(): array
    {
        return $this-><?= lcfirst($entityName) ?>s;
    }

    /**
    * Ajoute un <?= $entityName ?> à la collection.
    *
    * @param <?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst($entityName) ?>
    *
    * @return self
    */
    public function add<?= $entityName ?>(<?= basename(str_replace('\\', '/', $entityClass)) ?> $<?= lcfirst($entityName) ?>): self
    {
        $this-><?= lcfirst($entityName) ?>s[] = $<?= lcfirst($entityName) ?>;
        return $this;
    }

    /**
    * Vérifie si la collection est vide.
    *
    * @return bool
    */
    public function isEmpty(): bool
    {
        return count($this-><?= lcfirst($entityName) ?>s) === 0;
    }

    /**
    * Retourne les noms de tous les <?= $entityName ?>s de la collection.
    *
    * @return string[]
    */
    public function getNames(): array
    {
        return array_map(fn(<?= basename(str_replace('\\', '/', $entityClass)) ?> $e) => $e->getName(), $this-><?= lcfirst($entityName) ?>s);
    }

    /**
    * Retourne le nombre d’éléments dans la collection.
    *
    * @return int
    */
    public function count(): int
    {
        return count($this-><?= lcfirst($entityName) ?>s);
    }

    /**
    * Retourne un itérateur sur les éléments de la collection.
    *
    * @return \Traversable<int, <?= basename(str_replace('\\', '/', $entityClass)) ?>>
    */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this-><?= lcfirst($entityName) ?>s);
    }
}
