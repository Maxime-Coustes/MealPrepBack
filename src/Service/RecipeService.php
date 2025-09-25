<?php

namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeCollection;
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


    public function getAllRecipes(): RecipeCollection
    {
        $recipes = $this->repository->findAllWithIngredients();
        return new RecipeCollection($recipes);
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
     * @param array<string, mixed> $recipePayload
     * @return array{created: Recipe}|array{conflict: string}
     *
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    // public function create(array $recipePayload): array
    // {
    //     // Vérifie si la recette existe déjà
    //     $recipeAlreadyExists = $this->checkIfExists($recipePayload);

    //     if ($recipeAlreadyExists) {
    //         return [
    //             'conflict' => $recipePayload['name'],
    //         ];
    //     }

    //     // Sinon, on crée une nouvelle recette
    //     $recipeToCreate = new Recipe();
    //     $recipeToCreate->setName($recipePayload['name']);
    //     $recipeToCreate->setPreparation($recipePayload['preparation'] ?? null);

    //     foreach ($recipePayload['recipeIngredients'] as $recipeIngredientsData) {
    //         $ingredient = $this->findIngredient($recipeIngredientsData['ingredient'] ?? 0);
    //         if (!$ingredient) {
    //             throw new BadRequestHttpException("Ingredient with id {$recipeIngredientsData['ingredient']} not found");
    //         }

    //         $recipeIngredient = new RecipeIngredient();
    //         $recipeIngredient->setIngredient($ingredient);
    //         $recipeIngredient->setRecipe($recipeToCreate);
    //         $recipeIngredient->setQuantity(floatval($recipeIngredientsData['quantity'] ?? 0));
    //         $recipeIngredient->setUnit($recipeIngredientsData['unit'] ?? '');

    //         $recipeToCreate->addRecipeIngredient($recipeIngredient);
    //     }

    //     $this->repository->createRecipe($recipeToCreate);

    //     return [
    //         'created' => $recipeToCreate,
    //     ];
    // }
    /**
     * Crée une nouvelle recette et ses RecipeIngredient associés.
     *
     * @param array<string, mixed> $recipePayload
     * @return array{created: Recipe}|array{conflict: string}
     *
     * @throws BadRequestHttpException
     * @throws \Throwable
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

        // Instanciation dynamique de la bonne entité via Reflection
        $entityClass = $this->repository->getEntityClass();
        $recipeToCreate = new $entityClass();
        // Reflection pour déterminer les colonnes Doctrine
        $reflection = new \ReflectionClass($entityClass);
        $columns = [];
        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(\Doctrine\ORM\Mapping\Column::class);
            if (!empty($attrs)) {
                $columns[] = $property->getName();
            }
        }
        // On remplit dynamiquement les propriétés scalaires
        foreach ($columns as $column) {
            if (array_key_exists($column, $recipePayload)) {
                $setter = 'set' . ucfirst($column);
                if (method_exists($recipeToCreate, $setter)) {
                    $recipeToCreate->$setter($recipePayload[$column]);
                }
            }
        }

        // Cas particulier : gestion des relations (RecipeIngredients)
        foreach ($recipePayload['recipeIngredients'] ?? [] as $recipeIngredientsData) {
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

        // Persistance
        $this->repository->createRecipe($recipeToCreate);

        return [
            'created' => $recipeToCreate,
        ];
    }



    /**
     * @param array<string, mixed> $recipePayload
     * @return bool
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
     * @param array<string, mixed> $payload
     * @return array{
     *   recipe: Recipe,
     *   nameChanged: bool,
     *   preparationChanged: bool,
     *   added: RecipeIngredient[],
     *   updated: RecipeIngredient[],
     *   removed: RecipeIngredient[],
     *   message: string
     * }
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
     * Synchronise les ingrédients d'une recette avec ceux du payload.
     *
     * @param Recipe $recipe
     * @param array<int, array<string, mixed>> $payloadIngredients
     * @return array{RecipeIngredient[], RecipeIngredient[], RecipeIngredient[]} [$added, $updated, $removed]
     */
    private function syncIngredients(Recipe $recipe, array $payloadIngredients): array
    {
        $existingIngredients = $this->mapExistingIngredients($recipe);
        $payloadMap = $this->mapPayloadIngredients($payloadIngredients);

        $removed = $this->processRemovedIngredients($recipe, $existingIngredients, $payloadMap);
        [$added, $updated] = $this->processAddedOrUpdatedIngredients($recipe, $existingIngredients, $payloadMap);

        return [$added, $updated, $removed];
    }

    /**
     * Crée une map des ingrédients existants d'une recette pour un accès rapide par ID.
     *
     * @param Recipe $recipe La recette dont on veut mapper les ingrédients
     * @return array<int, RecipeIngredient> Tableau associatif [ingredientId => RecipeIngredient]
     */
    private function mapExistingIngredients(Recipe $recipe): array
    {
        $map = [];
        foreach ($recipe->getRecipeIngredients() as $ri) {
            $ingredient = $ri->getIngredient();
            if ($ingredient?->getId() !== null) {
                $map[$ingredient->getId()] = $ri;
            }
        }
        return $map;
    }

    /**
     * Crée une map des ingrédients fournis dans le payload pour un accès rapide par ID.
     *
     * @param array<int, array<string, mixed>> $payloadIngredients Tableau d'ingrédients du payload
     * @return array<int, array<string, mixed>> Tableau associatif [ingredientId => données de l'ingrédient]
     */
    private function mapPayloadIngredients(array $payloadIngredients): array
    {
        $map = [];
        foreach ($payloadIngredients as $i) {
            $map[$i['id']] = $i;
        }
        return $map;
    }

    /**
     * Identifie et supprime les ingrédients qui existent dans la recette mais ne sont plus présents dans le payload.
     *
     * @param Recipe $recipe La recette dont on traite les ingrédients
     * @param array<int, RecipeIngredient> $existing Map des ingrédients existants
     * @param array<int, array<string, mixed>> $payloadMap Map des ingrédients du payload
     * @return RecipeIngredient[] Liste des ingrédients supprimés
     */
    private function processRemovedIngredients(Recipe $recipe, array $existing, array $payloadMap): array
    {
        $removed = [];
        foreach ($existing as $id => $ri) {
            if (!isset($payloadMap[$id])) {
                $recipe->removeRecipeIngredient($ri);
                $removed[] = $ri;
            }
        }
        return $removed;
    }

    /**
     *  * Traite les ingrédients à ajouter ou à mettre à jour dans une recette.
     *
     * Cette méthode compare les ingrédients existants avec ceux provenant du payload :
     *   - Si l'ingrédient existe déjà, sa quantité et son unité sont mises à jour si nécessaire.
     *   - Si l'ingrédient n'existe pas encore, il est créé et ajouté à la recette.
     *
     * @param Recipe $recipe
     * @param array<int, RecipeIngredient> $existing
     * @param array<int, array<string, mixed>> $payloadMap
     * @return array{RecipeIngredient[], RecipeIngredient[]}
     */
    private function processAddedOrUpdatedIngredients(Recipe $recipe, array $existing, array $payloadMap): array
    {
        $added = [];
        $updated = [];

        foreach ($payloadMap as $id => $data) {
            if (isset($existing[$id])) {
                $ri = $existing[$id];
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

        return [$added, $updated];
    }


    /**
     * @param RecipeIngredient $ri
     * @param array<string, mixed> $data
     * @return bool
     */
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
