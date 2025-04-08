<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        // dd($ingredientsCollection);
        $em = $this->getEntityManager();

        foreach ($ingredientsCollection->getIngredients() as $ingredient) {

            // Utilisation des données du tableau associatif pour remplir l'objet
            $ingredient->setName($ingredient->getName());
            $ingredient->setUnite($ingredient->getUnite());
            $ingredient->setProteins($ingredient->getProteins());
            $ingredient->setFat($ingredient->getFat());
            $ingredient->setGlucides($ingredient->getGlucides());
            $ingredient->setCalories($ingredient->getCalories());

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

    public function findByName(string $name): ?IngredientCollection
    {
        // return $this->findOneBy(['name' => $name]);
        // return $this->createQueryBuilder('i')
        // ->where('i.name LIKE :name')
        // ->setParameter('name', $name . '%')
        // ->getQuery()
        // ->getOneOrNullResult();
        $results = $this->createQueryBuilder('i')
            ->where('LOWER(i.name) LIKE LOWER(:name)')
            ->setParameter('name', $name . '%')
            ->getQuery()
            ->getResult();

        $ingredientsCollection = new IngredientCollection();

        foreach ($results as $ingredient) {
            $ingredientsCollection->addIngredient($ingredient);
        }

        return $ingredientsCollection;
    }

    public function deleteIngredients(IngredientCollection $ingredientCollection): void
    {
        $em = $this->getEntityManager();
        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $em->remove($ingredient);
        }
    
        $em->flush();
    }

    /**
     * Met à jour une collection d'ingrédients dans la base de données.
     *
     * @param IngredientCollection $ingredientCollection
     * @return IngredientCollection Liste des ingrédients mis à jour
     */
    public function updateIngredients(IngredientCollection $ingredientCollection): IngredientCollection
    {
        $em = $this->getEntityManager();
        $updatedIngredients = new IngredientCollection();

        // Parcours chaque ingrédient dans la collection
        foreach ($ingredientCollection->getIngredients() as $ingredient) {
            $existingIngredient = $this->findOneByName($ingredient->getName());  // Trouve l'ingrédient par son name

            if (!$existingIngredient) {
                // faire un create plutot ?
                // $this->createIngredients($ingredient);
                throw new NotFoundHttpException('Ingredient not found with name ' . $ingredient->getName());
            }

            // Met à jour les propriétés de l'ingrédient
            $existingIngredient->setName($ingredient->getName());
            $existingIngredient->setUnite($ingredient->getUnite());
            $existingIngredient->setProteins($ingredient->getProteins());
            $existingIngredient->setFat($ingredient->getFat());
            $existingIngredient->setGlucides($ingredient->getGlucides());
            $existingIngredient->setCalories($ingredient->getCalories());

            // Sauvegarde l'ingrédient mis à jour
            $em->flush();

            // Ajoute l'ingrédient mis à jour à la collection
            $updatedIngredients->addIngredient($existingIngredient);
        }

        return $updatedIngredients;
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
