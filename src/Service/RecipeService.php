<?php

namespace App\Service;

use App\Entity\Recipe;
use App\Interface\RecipeServiceInterface;
use App\Repository\RecipeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecipeService implements RecipeServiceInterface
{
    private RecipeRepository $repository;

    public function __construct(RecipeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Retourne toutes les recettes avec leurs ingrédients pour le JSON.
     *
     * @return array
     */
    public function getAllRecipes(): array
    {
        $recipes = $this->repository->findAllWithIngredients();
        $data = [];
        foreach ($recipes as $recipe) {
            $ingredients = [];
            foreach ($recipe->getRecipeIngredients() as $recipeIngredient) {
                $ingredient = $recipeIngredient->getIngredient();
                $recipeIngredient->getIngredient()->getName();

                // Protection au cas où un ingrédient est manquant
                if (!$ingredient) {
                    continue;
                }

                $ingredients[] = [
                    'id' => $ingredient->getId(),
                    'name' => $ingredient->getName(),
                    'quantity' => $recipeIngredient->getQuantity(),
                    'unit' => $recipeIngredient->getUnit(),
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

    /**
     * @param integer $id
     * @return void
     */
    public function deleteRecipeById(int $id): void
    {
        $recipe = $this->repository->find($id);

        if (!$recipe) {
            throw new NotFoundHttpException(sprintf('Recette avec l\'id %d non trouvée.', $id));
        }

        $this->repository->deleteRecipe($recipe);
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
        return $this->repository->find($id);
    }
}
