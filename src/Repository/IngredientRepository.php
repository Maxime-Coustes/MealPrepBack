<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ingredient>
 */
// DAO
class IngredientRepository extends ServiceEntityRepository
{

    /**
     * ManagerRegistry allowed me to access to find($id), findAll(), findBy([...])...
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    public function createIngredients(IngredientCollection $ingredientsCollection): void
    {
        foreach ($ingredientsCollection->getIngredients() as $ingredient) {
            // Persister directement chaque ingrédient
            $this->getEntityManager()->persist($ingredient);
        }

        // Sauvegarde en base après avoir persisté tous les objets
        $this->getEntityManager()->flush();
    }

    public function getAllIngredients(): array
    {
        return $this->ingredientRepository->findAll();
    }

    public function findMultipleByName(string $name): ?IngredientCollection
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

    public function findOneByName(string $name): ?Ingredient
    {
        return $this->createQueryBuilder('i')
            ->where('i.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function deleteMultipleIngredients(IngredientCollection $ingredientCollection): void
    {
        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $this->getEntityManager()->remove($ingredient);
        }

        $this->getEntityManager()->flush();
    }

    public function deleteSingleIngredientById(Ingredient $ingredient): void
    {

        $this->getEntityManager()->remove($ingredient);
        $this->getEntityManager()->flush();
    }


    public function updateIngredients(IngredientCollection $ingredientCollection): IngredientCollection
    {

        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $this->getEntityManager()->persist($ingredient);
        }

        $this->getEntityManager()->flush();

        return $ingredientCollection;
    }
}
