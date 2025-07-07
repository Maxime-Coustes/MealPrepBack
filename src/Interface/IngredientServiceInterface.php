<?php
namespace App\Interface;

use App\Entity\IngredientCollection;

interface IngredientServiceInterface
{
    public function createIngredients(IngredientCollection $ingredients): array;
    public function getIngredientsList(): array;
    public function getMultipleIngredientsByName(string $name): ?IngredientCollection;
    public function deleteIngredients(IngredientCollection $ingredientCollection): void;
    public function updateIngredients(IngredientCollection $ingredients): array;
}
