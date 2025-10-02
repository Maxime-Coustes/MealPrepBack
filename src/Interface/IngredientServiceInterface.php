<?php

namespace App\Interface;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;

interface IngredientServiceInterface
{

    /**
     * @param array ingredients
     * @return array
     */
    public function createIngredients(array $ingredients): array;
    /**
     *
     * @return IngredientCollection
     */
    public function getIngredientsList(): IngredientCollection;
    /**
     *
     * @param string $name
     * @return IngredientCollection
     */
    public function getMultipleIngredientsByName(string $name): IngredientCollection;
    /**
     *
     * @param IngredientCollection $ingredientCollection
     * @return void
     */
    public function deleteMultipleIngredients(IngredientCollection $ingredientCollection): void;
    /**
     *
     * @param Ingredient $ingredient
     * @return void
     */
    public function deleteSingleIngredientById(Ingredient $ingredient): void;
    /**
     *
     * @param IngredientCollection $ingredients
     * @return array{updated: IngredientCollection, not_found: IngredientCollection}
     */
    public function updateIngredients(IngredientCollection $ingredients): array;
    /**
     *
     * @param string $ingredientName
     * @return Ingredient|null
     */
    public function findOneByName(string $ingredientName): ?Ingredient;
    /**
     *
     * @param integer $id
     * @return Ingredient|null
     */
    public function findOneById(int $id): ?Ingredient;
}
