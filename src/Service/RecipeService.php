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
        // Vérifie si la recette existe déjà
        $recipeAlreadyExists = $this->checkIfExists($recipePayload);

        if ($recipeAlreadyExists) {
            return [
                'conflict' => $recipePayload['name'],
            ];
        }

        // Sinon, on crée une nouvelle recette
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

        return [
            'created' => $recipeToCreate,
        ];
    }


    /**
     * @param array $recipePayload
     * @return boolean
     */
    private function checkIfExists(array $recipePayload): bool
    {
        // Vérifie si un ingrédient avec le même name existe déjà dans la base
        $existingRecipe = $this->repository->findOneBy(['name' => $recipePayload['name']]);

        // Si l'ingrédient existe déjà, on retourne true
        return $existingRecipe !== null;
    }

    /**
     * @param Recipe $recipe
     * @param array $payload
     * @return array{recipe: Recipe, nameChanged: bool, preparationChanged: bool, added: array, updated: array, removed: array, message: string}
     */
    public function update(Recipe $recipe, array $payload): array
    {
        $nameChanged = $this->updateRecipeName($recipe, $payload['name']);
        $preparationChanged = $this->updateRecipePreparation($recipe, $payload['preparation'] ?? null);

        [$added, $updated, $removed] = $this->syncIngredients($recipe, $payload['ingredients'] ?? []);

        $this->repository->update($recipe);

        $message = ($nameChanged || $preparationChanged || count($added) || count($updated) || count($removed))
            ? 'Recipe updated successfully'
            : 'No changes were made';

        return compact('recipe', 'nameChanged', 'preparationChanged', 'added', 'updated', 'removed', 'message');
    }

    /** @return bool */
    private function updateRecipeName(Recipe $recipe, string $newName): bool
    {
        if ($recipe->getName() === $newName) {
            return false;
        }
        $recipe->setName($newName);
        return true;
    }

    /** @return bool */
    private function updateRecipePreparation(Recipe $recipe, ?string $newPreparation): bool
    {
        if ($recipe->getPreparation() === $newPreparation) {
            return false;
        }
        $recipe->setPreparation($newPreparation);
        return true;
    }

    /**
     * @param Recipe $recipe
     * @param array<int, array> $payloadIngredients
     * @return array{array, array, array} [$added, $updated, $removed]
     */
    private function syncIngredients(Recipe $recipe, array $payloadIngredients): array
    {
        $added = $updated = $removed = [];

        $existingIngredients = [];
        foreach ($recipe->getRecipeIngredients() as $ri) {
            $existingIngredients[$ri->getIngredient()->getId()] = $ri;
        }

        $payloadMap = [];
        foreach ($payloadIngredients as $i) {
            $payloadMap[$i['id']] = $i;
        }

        // Removed
        foreach ($existingIngredients as $id => $ri) {
            if (!isset($payloadMap[$id])) {
                $recipe->removeRecipeIngredient($ri);
                $removed[] = $ri;
            }
        }

        // Added / updated
        foreach ($payloadMap as $id => $data) {
            if (isset($existingIngredients[$id])) {
                $ri = $existingIngredients[$id];
                if ($this->updateRecipeIngredient($ri, $data)) {
                    $updated[] = $ri;
                }
            } else {
                $ingredient = $this->ingredientService->findOneById($id);
                if (!$ingredient) {
                    continue;
                }
                $ri = new RecipeIngredient();
                $ri->setIngredient($ingredient)
                    ->setQuantity(floatval($data['quantity'] ?? 0))
                    ->setUnit($data['unit'] ?? '');
                $recipe->addRecipeIngredient($ri);
                $added[] = $ri;
            }
        }

        return [$added, $updated, $removed];
    }

    /** @return bool si la quantité/unit a changé */
    private function updateRecipeIngredient(RecipeIngredient $ri, array $data): bool
    {
        $changed = false;

        $qty = floatval($data['quantity'] ?? 0);
        if ($ri->getQuantity() !== $qty) {
            $ri->setQuantity($qty);
            $changed = true;
        }

        $unit = $data['unit'] ?? '';
        if ($ri->getUnit() !== $unit) {
            $ri->setUnit($unit);
            $changed = true;
        }

        return $changed;
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
