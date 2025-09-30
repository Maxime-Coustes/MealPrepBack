<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use Doctrine\Persistence\ManagerRegistry;

class IngredientRepository extends AbstractSolidRepository
{

    /**
     * ManagerRegistry allowed me to access to find($id), findAll(), findBy([...])...
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    /**
     * @param IngredientCollection $ingredientsCollection
     * @return void
     */
    public function createIngredients(IngredientCollection $ingredientsCollection): void
    {
        foreach ($ingredientsCollection->getIngredients() as $ingredient) {
            // Persister directement chaque ingrédient
            $this->getEntityManager()->persist($ingredient);
        }

        // Sauvegarde en base après avoir persisté tous les objets
        $this->getEntityManager()->flush();
    }

    /**
     * @return IngredientCollection
     */
    public function getAllIngredients(): IngredientCollection
    {
        $results = $this->findAll();
        return new IngredientCollection($results);
    }

    /**
     * @param string $name
     * @return IngredientCollection
     */
    public function findMultipleByName(string $name): IngredientCollection
    {
        $results = $this->createQueryBuilder('i')
            ->where('LOWER(i.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();

        $ingredientsCollection = new IngredientCollection();

        foreach ($results as $ingredient) {
            $ingredientsCollection->addIngredient($ingredient);
        }

        return $ingredientsCollection;
    }

    /**
     * @param string $name
     * @return Ingredient|null
     */
    public function findOneByName(string $name): ?Ingredient
    {
        return $this->createQueryBuilder('i')
            ->where('i.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param integer $id
     * @return Ingredient|null
     */
    public function findOneById(int $id): ?Ingredient
    {
        return $this->find($id);
    }

    /**
     * @param IngredientCollection $ingredientCollection
     * @return void
     */
    public function deleteMultipleIngredients(IngredientCollection $ingredientCollection): void
    {
        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $this->getEntityManager()->remove($ingredient);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param Ingredient $ingredient
     * @return void
     */
    public function deleteSingleIngredientById(Ingredient $ingredient): void
    {

        $this->getEntityManager()->remove($ingredient);
        $this->getEntityManager()->flush();
    }

    /**
     * @param IngredientCollection $ingredientCollection
     * @return IngredientCollection
     */
    public function updateIngredients(IngredientCollection $ingredientCollection): IngredientCollection
    {

        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $this->getEntityManager()->persist($ingredient);
        }

        $this->getEntityManager()->flush();

        return $ingredientCollection;
    }
}
