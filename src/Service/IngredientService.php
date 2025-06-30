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

    public function getIngredientsByName(string $name): ?IngredientCollection
    {
        return $this->ingredientRepository->findByName($name);
    }

    public function deleteIngredients(IngredientCollection $ingredientCollection): void
    {
        $this->ingredientRepository->deleteIngredients($ingredientCollection);
    }

    public function updateIngredients(IngredientCollection $ingredients): array
    {
        $toUpdate = new IngredientCollection();
        $notFound = new IngredientCollection();

        foreach ($ingredients as $ingredient) {
            $existing = $this->ingredientRepository->findOneByName($ingredient->getName());
            if (!$existing) {
                $notFound->addIngredient($ingredient);
                continue;
            }

            // Vérifie s'il y a un changement
            $hasChanged =
                $existing->getUnit() !== $ingredient->getUnit() ||
                $existing->getProteins() !== $ingredient->getProteins() ||
                $existing->getFat() !== $ingredient->getFat() ||
                $existing->getCarbs() !== $ingredient->getCarbs() ||
                $existing->getCalories() !== $ingredient->getCalories();

            if (!$hasChanged) {
                continue; // Ne pas l’ajouter à la liste à mettre à jour
            }

            // On met à jour les valeurs de l'entité existante
            $existing->setUnit($ingredient->getUnit());
            $existing->setProteins($ingredient->getProteins());
            $existing->setFat($ingredient->getFat());
            $existing->setCarbs($ingredient->getCarbs());
            $existing->setCalories($ingredient->getCalories());
            $toUpdate->addIngredient($existing);
        }

        $this->ingredientRepository->updateIngredients($toUpdate);

        return [
            'updated' => $toUpdate,
            'not_found' => $notFound,
        ];
    }
}
