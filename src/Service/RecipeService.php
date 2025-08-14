<?php

namespace App\Service;

use App\Entity\Recipe;
use App\Interface\RecipeServiceInterface;
use App\Repository\RecipeRepository;

class RecipeService implements RecipeServiceInterface
{
    private RecipeRepository $repository;

    public function __construct(RecipeRepository $repository)
    {
        $this->repository = $repository;
    }

    // Exemple de méthodes avec l’entité
    public function create(Recipe $recipe): void
    {
        exit;
    }

    public function update(Recipe $recipe): void
    {
        exit;
    }

    public function delete(Recipe $recipe): void
    {
        exit;
    }

    public function find(int $id): ?Recipe
    {
        exit;
    }
}
