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
    #[Route('/hello', name: 'app_recipe')]
    public function index(): Response
    {
        return $this->render('recipe/index.html.twig', [
            'controller_name' => 'RecipeController',
        ]);
    }

    #[Route('/recipes', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->recipeService->getAllRecipes());
    }


    /**
     * Crée une nouvelle recette à partir d'un payload JSON.
     *
     * @param Request $request Le payload JSON contenant 'name' et 'recipeIngredients'
     *
     * @return JsonResponse La réponse JSON avec l'id, le nom de la recette ou une erreur
     */
    #[Route('/recipe', name: 'create', methods: ['POST'])]
    public function createAction(Request $request): JsonResponse
    {
        $recipePayload = json_decode($request->getContent(), true);

        $response = [];
        $statusCode = Response::HTTP_OK;
        $requiredFields = ['name', 'recipeIngredients', 'preparation'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($recipePayload[$field]) || (is_string($recipePayload[$field]) && trim($recipePayload[$field]) === '')) {
                $missingFields[] = $field;
            }
        }

        if (!$recipePayload || !empty($missingFields)) {
            $errors = array_map(fn($f) => "Field '$f' is mandatory", $missingFields);
            $response = ['error' => $errors];
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            try {
                $result = $this->recipeService->create($recipePayload);

                [$response, $statusCode] = match (true) {
                    isset($result['conflict']) => [
                        [
                            'error' => 'Recipe already exists',
                            'conflict' => $result['conflict'],
                        ],
                        Response::HTTP_CONFLICT,
                    ],
                    isset($result['created']) => [
                        [
                            'id' => $result['created']->getId(),
                            'name' => $result['created']->getName(),
                            'message' => 'Recipe created successfully',
                        ],
                        Response::HTTP_CREATED,
                    ],
                    default => [
                        ['error' => 'Unexpected service response'],
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                    ]
                };
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
