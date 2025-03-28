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
            $ingredient->setNom($ingredient->getNom());
            $ingredient->setUnite($ingredient->getUnite());
            $ingredient->setProteines($ingredient->getProteines());
            $ingredient->setLipides($ingredient->getLipides());
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

    public function findOneByNom(string $nom): ?Ingredient
    {
        return $this->findOneBy(['nom' => $nom]);
    }

    public function deleteIngredient(Ingredient $ingredient): void 
    {
        $em = $this->getEntityManager();

        $em->remove($ingredient);
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
            $existingIngredient = $this->findOneByNom($ingredient->getNom());  // Trouve l'ingrédient par son nom

            if (!$existingIngredient) {
                // faire un create plutot ?
                // $this->createIngredients($ingredient);
                throw new NotFoundHttpException('Ingredient not found with name ' . $ingredient->getNom());
            }

            // Met à jour les propriétés de l'ingrédient
            $existingIngredient->setNom($ingredient->getNom());
            $existingIngredient->setUnite($ingredient->getUnite());
            $existingIngredient->setProteines($ingredient->getProteines());
            $existingIngredient->setLipides($ingredient->getLipides());
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
