<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * Récupère toutes les recettes avec leurs RecipeIngredient et Ingredient.
     *
     * @return Recipe[]
     */
    public function findAllWithIngredients(): array
    {
        return $this->createQueryBuilder('query')
            ->leftJoin('query.recipeIngredients', 'ri')
            ->addSelect('ri')
            ->getQuery()
            ->getResult();
    }

    /**
     * ACID: everything in a transcation, if the remove failed, nothing is applied
     *
     * @param Recipe $recipe
     * @return void
     */
    public function deleteRecipe(Recipe $recipe): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $conn->beginTransaction(); // ACID

        try {
            // Supprime les RecipeIngredient liés
            foreach ($recipe->getRecipeIngredients() as $ri) {
                $this->getEntityManager()->remove($ri);
            }
        
            $this->getEntityManager()->remove($recipe);
            $this->getEntityManager()->flush();
            $conn->commit();
        } catch (\Throwable $e) {
            // Vérification avant rollback
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    public function createRecipe(Recipe $recipe): void
    {
        $this->getEntityManager()->persist($recipe);
        $this->getEntityManager()->flush();
    }
}
