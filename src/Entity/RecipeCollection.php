<?php

namespace App\Entity;

use ArrayIterator;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<int, Recipe>
 */
class RecipeCollection implements IteratorAggregate
{
    /** @var Recipe[] */
    private array $recipes;

    /**
     * @param Recipe[] $recipes
     */
    public function __construct(array $recipes = [])
    {
        $this->recipes = $recipes;
    }

    /**
     * @return ArrayIterator<int, Recipe>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->recipes);
    }

    /**
     * Retourne les recettes sous forme de tableau strictement typé.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     preparation: string|null,
     *     ingredients: array<int, array{id: int, name: string, quantity: float, unit: string}>
     * }>
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->recipes as $recipe) {
            // Skip si la recette n'est pas persistée ou nom manquant
            if ($recipe->getId() === null || empty($recipe->getName())) {
                continue;
            }

            $ingredients = [];
            foreach ($recipe->getRecipeIngredients() as $ri) {
                $ingredient = $ri->getIngredient();
                if (!$ingredient || $ingredient->getId() === null || empty($ingredient->getName())) {
                    continue;
                }

                $ingredients[] = [
                    'id' => $ingredient->getId(),
                    'name' => $ingredient->getName(),
                    'quantity' => $ri->getQuantity() ?? 0.0,
                    'unit' => $ri->getUnit() ?? '',
                ];
            }

            $data[] = [
                'id' => $recipe->getId(),
                'name' => $recipe->getName(),
                'preparation' => $recipe->getPreparation(),
                'ingredients' => $ingredients,
            ];
        }

        return $data;
    }
}
