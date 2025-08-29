<?php

namespace App\Controller;

use App\Interface\RecipeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{

    private RecipeServiceInterface $recipeService;
    public function __construct(
        RecipeServiceInterface $recipeService
    ) {
        $this->recipeService = $recipeService;
    }

    #[Route('/recipes', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->recipeService->getAllRecipes());
    }

    #[Route('/hello', name: 'app_recipe')]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    /**
     * Crée une nouvelle recette à partir d'un payload JSON.
     *
     * @param Request $request Le payload JSON contenant 'name' et 'recipeIngredients'
     *
     * @return JsonResponse La réponse JSON avec l'id, le nom de la recette ou une erreur
     */
    #[Route('/recipe', name: 'create', methods: ['POST'])]
    public function createAction(Request $request): Response
    {
        $recipePayload = json_decode($request->getContent(), true);
        $response = null;
        $statusCode = Response::HTTP_OK;
        if (!$recipePayload || !isset($recipePayload['name'], $recipePayload['recipeIngredients'])) {
            $response = ['error' => 'Invalid data'];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            try {
                $recipeToCreate = $this->recipeService->create($recipePayload);
                $response = [
                    'id' => $recipeToCreate['created']->getId(),
                    'name' => $recipeToCreate['created']->getName(),
                    'message' => 'Recipe created successfully',
                ];
                $statusCode = Response::HTTP_CREATED;
            } catch (\Throwable $e) {
                $response = [
                    'error' => 'Unexpected error',
                    'details' => $e->getMessage(),
                ];
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }
        return $this->json($response, $statusCode);
    }

    /**
     * Supprime une recette existante par son ID.
     *
     * @param int $id ID de la recette à supprimer
     *
     * @return JsonResponse La réponse JSON confirmant la suppression ou une erreur
     */
    #[Route('/recipes/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteAction(int $id): JsonResponse
    {
        try {
            $recipeToDelete = $this->recipeService->find($id);
            $this->recipeService->deleteRecipeById($id);

            return new JsonResponse([
                'message' => 'recipe with id ' . $id . ' successfully deleted.',
                'deletedRecipe' => $recipeToDelete->getName(),
                'statusCode' => Response::HTTP_OK
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
