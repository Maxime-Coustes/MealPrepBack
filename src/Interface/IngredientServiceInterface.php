<?php
namespace App\Interface;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;

interface IngredientServiceInterface
{
    public function createIngredients(IngredientCollection $ingredients): bool;
    public function getIngredientsListAction(): array;
    public function getIngredientByName(string $nom): ?Ingredient;
    public function deleteIngredient(Ingredient $ingredient): void;
}
