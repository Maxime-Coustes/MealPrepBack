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

    public function updateIngredients(IngredientCollection $ingredientCollection): IngredientCollection
    {
        $updatedIngredients = $this->ingredientRepository->updateIngredients($ingredientCollection);
        return $updatedIngredients;
    }
}
