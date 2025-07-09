<?php

namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use App\Repository\IngredientRepository;
use App\Interface\IngredientServiceInterface;

class IngredientService implements IngredientServiceInterface
{
    private $ingredientRepository;

    public function __construct(IngredientRepository $ingredientRepository)
    {
        $this->ingredientRepository = $ingredientRepository;
    }

    public function createIngredients(IngredientCollection $ingredientsCollection): array
    {
        $newIngredientCollection = new IngredientCollection();
        $existing = new IngredientCollection();

        foreach ($ingredientsCollection->getIngredients() as $ingredient) {
            // Vérifie si l'ingrédient existe déjà
            $exist = $this->checkIfExists($ingredient);

            if ($exist) {
                $existing->addIngredient($ingredient);
                continue;
            } else {
                // Si l'ingrédient n'existe pas, on l'ajoute à la nouvelle collection
                $newIngredientCollection->addIngredient($ingredient);
                $this->ingredientRepository->createIngredients($newIngredientCollection);
            }
        }

        return [
            'created' => $newIngredientCollection,
            'existing' => $existing,
        ];
    }


    private function checkIfExists(Ingredient $ingredient): bool
    {
        // Vérifie si un ingrédient avec le même name existe déjà dans la base
        $existingIngredient = $this->ingredientRepository->findOneBy(['name' => $ingredient->getName()]);

        // Si l'ingrédient existe déjà, on retourne true
        return $existingIngredient !== null;
    }


    public function getIngredientsList(): array
    {
        return $this->ingredientRepository->findAll();
    }

    public function getMultipleIngredientsByName(string $name): ?IngredientCollection
    {
        return $this->ingredientRepository->findMultipleByName($name);
    }

    public function deleteIngredients(IngredientCollection $ingredientCollection): void
    {
        $this->ingredientRepository->deleteIngredients($ingredientCollection);
    }

    public function findOneByName(string $ingredientName): ?Ingredient
    {
        return $this->ingredientRepository->findOneByName($ingredientName);
    }

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

            $existing = $this->ingredientRepository->find($id);

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
