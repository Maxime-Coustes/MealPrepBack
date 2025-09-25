<?php

namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use App\Repository\IngredientRepository;
use App\Interface\IngredientServiceInterface;
use Src\Utils\DoctrineHelper;

class IngredientService implements IngredientServiceInterface
{
    private IngredientRepository $ingredientRepository;

    public function __construct(IngredientRepository $ingredientRepository)
    {
        $this->ingredientRepository = $ingredientRepository;
    }

    /**
     * @param array<int, array<string, mixed>> $ingredientsData
     * @return array{created: IngredientCollection, existing: IngredientCollection}
     */
    public function createIngredients(array $ingredientsData): array
    {
        $columns = DoctrineHelper::getDoctrineColumns($this->ingredientRepository->getEntityClass());

        $newIngredientCollection = new IngredientCollection();
        $existing = new IngredientCollection();

        foreach ($ingredientsData as $ingredientArray) {
            $ingredient = DoctrineHelper::populateEntityFromArray(Ingredient::class, $ingredientArray, false);

            $this->applyGenericRules($ingredient, $columns);

            if ($this->checkIfExists($ingredient)) {
                $existing->addIngredient($ingredient);
            } else {
                $newIngredientCollection->addIngredient($ingredient);
            }
        }

        if (!$newIngredientCollection->isEmpty()) {
            $this->ingredientRepository->createIngredients($newIngredientCollection);
        }


        return [
            'created' => $newIngredientCollection,
            'existing' => $existing,
        ];
    }


    /**
     * Applique les règles génériques sur un Ingredient.
     *
     * @param string[] $columns
     */
    private function applyGenericRules(Ingredient $ingredient, array $columns): void
    {
        foreach ($columns as $column) {
            $getter = 'get' . ucfirst($column);
            $setter = 'set' . ucfirst($column);

            if (!method_exists($ingredient, $getter) || !method_exists($ingredient, $setter)) {
                continue;
            }

            $value = $ingredient->$getter();

            if ($column === 'name' && $value !== null) {
                $value = ucfirst(strtolower($value));
            }

            $ingredient->$setter($value);
        }
    }

    /**
     * @param Ingredient $ingredient
     * @return boolean
     */
    private function checkIfExists(Ingredient $ingredient): bool
    {
        // Vérifie si un ingrédient avec le même name existe déjà dans la base
        $existingIngredient = $this->ingredientRepository->findOneBy(['name' => $ingredient->getName()]);

        // Si l'ingrédient existe déjà, on retourne true
        return $existingIngredient !== null;
    }


    /**
     * @return IngredientCollection
     */
    public function getIngredientsList(): IngredientCollection
    {
        return $this->ingredientRepository->getAllIngredients();
    }

    /**
     * @param string $name
     * @return IngredientCollection
     */
    public function getMultipleIngredientsByName(string $name): IngredientCollection
    {
        return $this->ingredientRepository->findMultipleByName($name);
    }

    /**
     * @param IngredientCollection $ingredientCollection
     * @return void
     */
    public function deleteMultipleIngredients(IngredientCollection $ingredientCollection): void
    {
        $this->ingredientRepository->deleteMultipleIngredients($ingredientCollection);
    }

    /**
     * @param Ingredient $ingredient
     * @return void
     */
    public function deleteSingleIngredientById(Ingredient $ingredient): void
    {
        $this->ingredientRepository->deleteSingleIngredientById($ingredient);
    }

    /**
     * @param integer $id
     * @return Ingredient|null
     */
    public function findOneById(int $id): ?Ingredient
    {
        return $this->ingredientRepository->findOneById($id);
    }

    /**
     * @param string $ingredientName
     * @return Ingredient|null
     */
    public function findOneByName(string $ingredientName): ?Ingredient
    {
        return $this->ingredientRepository->findOneByName($ingredientName);
    }

    /**
     * @param IngredientCollection $ingredients
     * @return array{updated: IngredientCollection, not_found: IngredientCollection}
     * @TODO : improve this method using new \ReflectionClass($this->repository->getEntityClass());
     */
    public function updateIngredients(IngredientCollection $ingredients): array
    {
        $toUpdate = new IngredientCollection();
        $notFound = new IngredientCollection();

        foreach ($ingredients as $ingredient) {
            $id = $ingredient->getId();

            if ($id === null) {
                $notFound->addIngredient($ingredient);
                continue;
            }

            $existing = $this->ingredientRepository->findOneById($id);

            if (!$existing) {
                $notFound->addIngredient($ingredient);
                continue;
            }

            // Vérifie si un champ a réellement changé
            $hasChanged =
                $existing->getName() !== $ingredient->getName() ||
                $existing->getUnit() !== $ingredient->getUnit() ||
                $existing->getProteins() !== $ingredient->getProteins() ||
                $existing->getFat() !== $ingredient->getFat() ||
                $existing->getCarbs() !== $ingredient->getCarbs() ||
                $existing->getCalories() !== $ingredient->getCalories();

            if (!$hasChanged) {
                continue;
            }

            // Mise à jour des propriétés
            $existing
                ->setName($ingredient->getName())
                ->setUnit($ingredient->getUnit())
                ->setProteins($ingredient->getProteins())
                ->setFat($ingredient->getFat())
                ->setCarbs($ingredient->getCarbs())
                ->setCalories($ingredient->getCalories());

            $toUpdate->addIngredient($existing);
        }

        $this->ingredientRepository->updateIngredients($toUpdate);

        return [
            'updated' => $toUpdate,
            'not_found' => $notFound
        ];
    }
}
