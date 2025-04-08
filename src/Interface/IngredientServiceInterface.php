<?php
namespace App\Interface;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;

interface IngredientServiceInterface
{
    public function createIngredients(IngredientCollection $ingredients): bool;
    public function getIngredientsListAction(): array;
    public function getIngredientByName(string $nom): ?IngredientCollection;
    public function deleteIngredients(IngredientCollection $ingredientCollection): void;
    public function updateIngredients(IngredientCollection $ingredients): IngredientCollection;
}
