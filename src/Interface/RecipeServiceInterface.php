<?php

namespace App\Interface;

use App\Entity\Recipe;
use App\Entity\RecipeCollection;

interface RecipeServiceInterface
{
    /**
     * Crée une recette.
     *
     * @param array<string, mixed> $recipe Tableau associatif des données de la recette
     * @return array<string, mixed> Statut ou données créées
     */
    public function create(array $recipe): array;

    /**
     * Met à jour une recette.
     *
     * @param Recipe $recipe
     * @param array<string, mixed> $payload Données de mise à jour
     * @return array<string, mixed> Détails des modifications
     */
    public function update(Recipe $recipe, array $payload): array;


    public function deleteRecipeById(int $id): void;

    public function find(int $id): ?Recipe;

    /**
     * Retourne toutes les recettes.
     *
     * @return RecipeCollection
     */
    public function getAllRecipes(): RecipeCollection;
}
