<?php

namespace App\Entity;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class IngredientCollection implements Countable, IteratorAggregate
{
    private array $ingredients = [];

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

    public function addIngredient(Ingredient $ingredient): self
    {
        $this->ingredients[] = $ingredient;
        return $this;
    }

    public function isEmpty(): bool
    {
        return count($this->ingredients) === 0;
    }

    public function getNames(): array
    {
        return array_map(fn(Ingredient $i) => $i->getName(), $this->ingredients);
    }

    public function count(): int
    {
        return count($this->ingredients);
    }

    // Pour qu'elle soit iterable aussi (foreach etc.)
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->ingredients);
    }

    /** avoid 
     * foreach ($ingredients as $ingredient) {
     *   $collection->addIngredient($ingredient);
     *} each time
     */
    public function addMany(array $ingredients): void
    {
        foreach ($ingredients as $ingredient) {
            $this->addIngredient($ingredient);
        }
    }


    // public function removeIngredient(Ingredient $ingredient): self
    // {
    //     $key = array_search($ingredient, $this->ingredients, true);
    //     if ($key !== false) {
    //         unset($this->ingredients[$key]);
    //     }
    //     return $this;
    // }
}
