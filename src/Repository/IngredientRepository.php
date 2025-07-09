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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    public function createIngredients(IngredientCollection $ingredientsCollection): void
    {
        $em = $this->getEntityManager();

        foreach ($ingredientsCollection->getIngredients() as $ingredient) {
            // Persister directement chaque ingrédient
            $em->persist($ingredient);
        }

        // Sauvegarde en base après avoir persisté tous les objets
        $em->flush();
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
        $em = $this->getEntityManager();
        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $em->remove($ingredient);
        }

        $em->flush();
    }

    public function deleteSingleIngredientById(Ingredient $ingredient): void
    {
        $em = $this->getEntityManager();

            $em->remove($ingredient);
            $em->flush();
    }


    public function updateIngredients(IngredientCollection $ingredientCollection): IngredientCollection
    {
        $em = $this->getEntityManager();

        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $em->persist($ingredient);
        }

        $em->flush();

        return $ingredientCollection;
    }


    //    /**
    //     * @return Ingredient[] Returns an array of Ingredient objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ingredient
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
