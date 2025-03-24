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
