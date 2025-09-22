<?php

namespace App\Entity;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Ingredient>
 */
class IngredientCollection implements Countable, IteratorAggregate
{
    /**
     * @var Ingredient[]
     */
    private array $ingredients = [];

    /**
     * @param Ingredient[] $ingredients
     */
    public function __construct(array $ingredients = [])
    {
        $this->ingredients = $ingredients;
    }

    /**
     * @return array|Ingredient[]
     */
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    /**
     *
     * @param Ingredient $ingredient
     * @return self
     */
    public function addIngredient(Ingredient $ingredient): self
    {
        $this->ingredients[] = $ingredient;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return count($this->ingredients) === 0;
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_map(fn(Ingredient $i) => $i->getName(), $this->ingredients);
    }

    /**
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->ingredients);
    }

    /**
     *
     * @return \Traversable<int, Ingredient>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->ingredients);
    }
}
