<?php

namespace App\Controller;

use App\Entity\RecipeCollection;
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

    /**
     * Récupère la liste de toutes les recettes.
     *
     * @return JsonResponse Liste des recettes au format JSON
     */
    #[Route('/recipes', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $recipes = $this->recipeService->getAllRecipes();
        return $this->json($recipes->toArray());
    }


    /**
     * Crée une nouvelle recette à partir du payload JSON fourni.
     *
     * Champs requis : name, recipeIngredients, preparation.
     *
     * @param Request $request Contient le payload JSON de la recette
     * @return JsonResponse Réponse JSON avec succès, conflit ou erreurs de validation
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
     * Supprime une recette existante par son identifiant.
     *
     * @param int $id Identifiant unique de la recette
     * @return JsonResponse Réponse JSON indiquant le succès ou l’erreur rencontrée
     */
    #[Route('/recipes/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteAction(int $id): JsonResponse
    {
        try {
            $recipeToDelete = $this->recipeService->find($id);
            if (!$recipeToDelete) {
                return $this->json([
                    'error' => "Recipe with id $id not found"
                ], Response::HTTP_NOT_FOUND);
            }
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

    /**
     * Met à jour une recette existante.
     *
     * Champs requis dans le payload : id, name, ingredients.
     *
     * @param Request $request Contient le payload JSON pour la mise à jour
     * @return JsonResponse Réponse JSON avec les changements appliqués ou un message indiquant qu’aucune modification n’a eu lieu
     */
    #[Route('/recipe', name: 'update', methods: ['PUT'])]
    public function updateRecipeAction(Request $request): JsonResponse
    {
        $response = [];
        $statusCode = JsonResponse::HTTP_OK;

        $content = $request->getContent();
        if (empty($content)) {
            $response = ['error' => 'Empty payload'];
            $statusCode = JsonResponse::HTTP_BAD_REQUEST;
        } else {
            $data = json_decode($content, true);
            if (!is_array($data)) {
                $response = ['error' => 'Invalid JSON'];
                $statusCode = JsonResponse::HTTP_BAD_REQUEST;
            } elseif (empty($data['id']) || empty($data['name']) || empty($data['ingredients'])) {
                $response = ['error' => 'Missing required fields: id, name, ingredients'];
                $statusCode = JsonResponse::HTTP_BAD_REQUEST;
            } else {
                $recipe = $this->recipeService->find($data['id']);
                if (!$recipe) {
                    $response = ['error' => 'Recipe not found'];
                    $statusCode = JsonResponse::HTTP_NOT_FOUND;
                } else {
                    try {
                        $result = $this->recipeService->update($recipe, $data);
                        $recipeCollection = new RecipeCollection([$result['recipe']]);
                        $recipeArray = $recipeCollection->toArray()[0];

                        $response = [
                            'message' => $result['message'],
                            'recipe' => $recipeArray,
                            'added' => array_map(fn($ri) => [
                                'id' => $ri->getIngredient()->getId(),
                                'name' => $ri->getIngredient()->getName(),
                                'quantity' => $ri->getQuantity(),
                                'unit' => $ri->getUnit(),
                            ], $result['added']),
                            'updated' => array_map(fn($ri) => [
                                'id' => $ri->getIngredient()->getId(),
                                'name' => $ri->getIngredient()->getName(),
                                'quantity' => $ri->getQuantity(),
                                'unit' => $ri->getUnit(),
                            ], $result['updated']),
                            'removed' => array_map(fn($ri) => [
                                'id' => $ri->getIngredient()->getId(),
                                'name' => $ri->getIngredient()->getName(),
                                'quantity' => $ri->getQuantity(),
                                'unit' => $ri->getUnit(),
                            ], $result['removed']),
                        ];
                    } catch (\Throwable $e) {
                        $response = ['error' => $e->getMessage()];
                        $statusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
                    }
                }
            }
        }

        return new JsonResponse($response, $statusCode);
    }
}
