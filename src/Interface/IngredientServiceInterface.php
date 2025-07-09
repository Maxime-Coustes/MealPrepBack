<?php
namespace App\Interface;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;

interface IngredientServiceInterface
{
    public function createIngredients(IngredientCollection $ingredients): array;
    public function getIngredientsList(): array;
    public function getMultipleIngredientsByName(string $name): ?IngredientCollection;
    public function deleteMultipleIngredients(IngredientCollection $ingredientCollection): void;
    public function deleteSingleIngredientById(Ingredient $ingredient): void;
    public function updateIngredients(IngredientCollection $ingredients): array;
    public function findOneByName(string $ingredientName): ?Ingredient;
    public function findOneById(int $id): ?Ingredient;
}
