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

    public function createIngredients(IngredientCollection $ingredientsCollection): bool
    {
        $newIngredientCollection = new IngredientCollection();
        $ingredientExists = false;

        foreach ($ingredientsCollection->getIngredients() as $ingredient) {
            // Vérifie si l'ingrédient existe déjà
            $exist = $this->checkIfExists($ingredient);

            if ($exist) {
                $ingredientExists = true; // Au moins un ingrédient existe déjà
            } else {
                // Si l'ingrédient n'existe pas, on l'ajoute à la nouvelle collection
                $newIngredientCollection->addIngredient($ingredient);
            }
        }

        // Si count() > 0 alors nous avons de nouveaux ingrédients et on les persist
        if (count($newIngredientCollection->getIngredients()) > 0) {
            $this->ingredientRepository->createIngredients($newIngredientCollection);
            return true;
        }

        // Si tous les ingrédients existaient déjà, on retourne false
        return !$ingredientExists;
    }


    private function checkIfExists(Ingredient $ingredient): bool
    {
        // Vérifie si un ingrédient avec le même nom existe déjà dans la base
        $existingIngredient = $this->ingredientRepository->findOneBy(['nom' => $ingredient->getNom()]);

        // Si l'ingrédient existe déjà, on retourne true
        return $existingIngredient !== null;
    }


    public function getIngredientsListAction(): array
    {
        return $this->ingredientRepository->findAll();
    }

    public function getIngredientByName(string $nom): ?Ingredient
    {
        return $this->ingredientRepository->findOneByNom($nom);
    }

    public function deleteIngredient(Ingredient $ingredient): void
    {
        $this->ingredientRepository->deleteIngredient($ingredient);
    }
}
