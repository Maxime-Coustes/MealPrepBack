<?php

namespace App\Interface;

use App\Entity\Recipe;

interface RecipeServiceInterface
{
    public function create(array $recipe): array;

    public function update(Recipe $recipe): void;

    public function deleteRecipeById(int $id): void;

    public function find(int $id): ?Recipe;

    public function getAllRecipes(): array;
}
