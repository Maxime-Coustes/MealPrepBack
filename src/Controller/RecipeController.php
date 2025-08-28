<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Repository\IngredientRepository;
use App\Service\RecipeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    private EntityManagerInterface $em;
    private IngredientRepository $ingredientRepository;

    public function __construct(
        IngredientRepository $ingredientRepository,
        EntityManagerInterface $em
    ) {
        $this->ingredientRepository = $ingredientRepository;
        $this->em = $em;
    }

    #[Route('/recipes', name: 'list', methods: ['GET'])]
    public function list(RecipeService $recipeService): JsonResponse
    {
        return $this->json($recipeService->getAllRecipes());
    }



    #[Route('/hello', name: 'app_recipe')]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    #[Route('/recipe', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'], $data['recipeIngredients'])) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $recipe = new Recipe();
        $recipe->setName($data['name']);
        $recipe->setPreparation($data['preparation'] ?? null);

        foreach ($data['recipeIngredients'] as $riData) {
            $ingredient = $this->ingredientRepository->find($riData['ingredient'] ?? 0);
            if (!$ingredient) {
                return $this->json(['error' => "Ingredient with id {$riData['ingredient']} not found"], Response::HTTP_BAD_REQUEST);
            }

            $recipeIngredient = new RecipeIngredient();
            $recipeIngredient->setIngredient($ingredient);
            $recipeIngredient->setRecipe($recipe);
            $recipeIngredient->setQuantity(floatval($riData['quantity'] ?? 0));
            $recipeIngredient->setUnit($riData['unit'] ?? '');

            $recipe->addRecipeIngredient($recipeIngredient);
        }

        $this->em->persist($recipe);
        $this->em->flush();

        return $this->json([
            'id' => $recipe->getId(),
            'message' => 'Recipe created successfully',
        ], Response::HTTP_CREATED);
    }

    #[Route('/recipes/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, RecipeService $recipeService): JsonResponse
    {
        try {
            $recipeService->deleteRecipeById($id);
            return new JsonResponse(null, 204); // ⬅️ No Content
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
