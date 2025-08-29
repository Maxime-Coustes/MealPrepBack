<?php

namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Interface\RecipeServiceInterface;
use App\Repository\RecipeRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class RecipeService implements RecipeServiceInterface
{
    private RecipeRepository $repository;
    private IngredientService $ingredientService;

    public function __construct(RecipeRepository $repository, IngredientService $ingredientService)
    {
        $this->repository = $repository;
        $this->ingredientService = $ingredientService;
    }

    /**
     * Récupère toutes les recettes avec leurs ingrédients sous forme de tableau.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     preparation: string|null,
     *     ingredients: array<int, array{
     *         id: int,
     *         name: string,
     *         quantity: float,
     *         unit: string
     *     }>
     * }>
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
     * Supprime une recette existante.
     *
     * @param int $id L'ID de la recette à supprimer
     *
     * @throws NotFoundHttpException Si la recette n'existe pas
     */
    public function deleteRecipeById(int $id): void
    {
        $recipe = $this->repository->find($id);

        if (!$recipe) {
            throw new NotFoundHttpException(sprintf('Recette avec l\'id %d non trouvée.', $id));
        }

        $this->repository->deleteRecipe($recipe);
    }

    /**
     * Crée une nouvelle recette et ses RecipeIngredient associés.
     *
     * @param array $recipePayload Tableau associatif avec :
     *  - 'name' => string
     *  - 'preparation' => string|null
     *  - 'recipeIngredients' => array[] (chaque élément contient 'ingredient', 'quantity', 'unit')
     *
     * @return array Contenant la recette créée : ['created' => Recipe]
     *
     * @throws BadRequestHttpException Si un ingredient n'est pas trouvé
     * @throws \Throwable Pour toute autre erreur inattendue
     */
    public function create(array $recipePayload): array
    {
        $recipeToCreate = new Recipe();
        $recipeToCreate->setName($recipePayload['name']);
        $recipeToCreate->setPreparation($recipePayload['preparation'] ?? null);

        foreach ($recipePayload['recipeIngredients'] as $recipeIngredientsData) {
            $ingredient = $this->findIngredient($recipeIngredientsData['ingredient'] ?? 0);
            if (!$ingredient) {
                throw new BadRequestHttpException("Ingredient with id {$recipeIngredientsData['ingredient']} not found");
            }

            $recipeIngredient = new RecipeIngredient();
            $recipeIngredient->setIngredient($ingredient);
            $recipeIngredient->setRecipe($recipeToCreate);
            $recipeIngredient->setQuantity(floatval($recipeIngredientsData['quantity'] ?? 0));
            $recipeIngredient->setUnit($recipeIngredientsData['unit'] ?? '');

            $recipeToCreate->addRecipeIngredient($recipeIngredient);
        }
        $this->repository->createRecipe($recipeToCreate);

        return ['created' => $recipeToCreate];
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

    /**
     * Récupère un ingrédient par son ID via le service Ingredient.
     *
     * @param int $id L'ID de l'ingrédient
     *
     * @return Ingredient|null L'ingrédient ou null s'il n'existe pas
     */
    public function findIngredient(int $id): ?Ingredient
    {
        return $this->ingredientService->findOneById($id);
    }
}
