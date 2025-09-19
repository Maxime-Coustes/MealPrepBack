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

    #[Route('/recipe', name: 'update', methods: ['PUT'])]
    public function updateRecipeAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $response = [];
        $statusCode = JsonResponse::HTTP_OK;

        // ✅ Vérification basique
        if (empty($data['id']) || empty($data['name']) || empty($data['ingredients'])) {
            $response = ['error' => 'Missing required fields: id, name, ingredients'];
            $statusCode = JsonResponse::HTTP_BAD_REQUEST;
        } else {
            try {
                // Appel du service pour gérer added / updated / removed
                // 🔹 Récupère l'entité Recipe existante
                $recipe = $this->recipeService->find($data['id']);

                // 🔹 Passe l'entité + payload au service update
                $result = $this->recipeService->update($recipe, $data);

                $response = [
                    'message' => count($result['added']) || count($result['updated']) || count($result['removed'])
                    || !empty($result['nameChanged']) || !empty($result['preparationChanged'])
                        ? 'Recipe updated successfully'
                        : 'No changes were made',
                    'nameChanged' => $result['nameChanged'],
                    'new_name' => $result['nameChanged'] ? $result['recipe']->getName() : null,
                    'preparationChanged' => $result['preparationChanged'],
                    'new_preparation' => $result['preparationChanged'] ? $result['recipe']->getPreparation() : null,
                    'added' => array_map(fn($ri) => [
                        'id' => $ri->getIngredient()->getId(),
                        'name' => $ri->getIngredient()->getName(),
                        'quantity' => $ri->getQuantity(),
                        'unit' => $ri->getUnit()
                    ], $result['added']),
                    'updated' => array_map(fn($ri) => [
                        'id' => $ri->getIngredient()->getId(),
                        'name' => $ri->getIngredient()->getName(),
                        'quantity' => $ri->getQuantity(),
                        'unit' => $ri->getUnit()
                    ], $result['updated']),
                    'removed' => array_map(fn($ri) => [
                        'id' => $ri->getIngredient()->getId(),
                        'name' => $ri->getIngredient()->getName(),
                        'quantity' => $ri->getQuantity(),
                        'unit' => $ri->getUnit()
                    ], $result['removed']),
                ];
            } catch (\Throwable $e) {
                $response = ['error' => $e->getMessage()];
                $statusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        return new JsonResponse($response, $statusCode);
    }
}
