<?php

namespace App\Entity;

use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, Tag>
 */
class TagCollection implements \Countable, \IteratorAggregate
{
    /** @var Tag[] */
    private array $tags = [];

    /**
     * @param Tag[] $tags
     */
    public function __construct(array $tags = [])
    {
        $this->tags = $tags;
    }

    /** @return Tag[] */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Ajoute un Tag à la collection.
     *
     * @param Tag $tag *
     */
    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Vérifie si la collection est vide.
     */
    public function isEmpty(): bool
    {
        return 0 === count($this->tags);
    }

    /**
     * Retourne les noms de tous les Tags de la collection.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return array_map(fn(Tag $e) => $e->getName(), $this->tags);
    }

    /**
     * Retourne le nombre d’éléments dans la collection.
     */
    public function count(): int
    {
        return count($this->tags);
    }

    /**
     * Retourne un itérateur sur les éléments de la collection.
     *
     * @return \Traversable<int, Tag>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->tags);
    }
}
