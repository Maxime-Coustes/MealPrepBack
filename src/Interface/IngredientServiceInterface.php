<?php
namespace App\Interface;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;

interface IngredientServiceInterface
{
    public function createIngredients(IngredientCollection $ingredients): bool;
    public function getIngredientsListAction(): array;
    public function getIngredientsByName(string $name): ?IngredientCollection;
    public function deleteIngredients(IngredientCollection $ingredientCollection): void;
    public function updateIngredients(IngredientCollection $ingredients): IngredientCollection;
}
