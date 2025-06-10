<?php

namespace App\Entity;

class IngredientCollection
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


    // public function removeIngredient(Ingredient $ingredient): self
    // {
    //     $key = array_search($ingredient, $this->ingredients, true);
    //     if ($key !== false) {
    //         unset($this->ingredients[$key]);
    //     }
    //     return $this;
    // }
}
