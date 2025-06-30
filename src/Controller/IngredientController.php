<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use App\Service\IngredientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController extends AbstractController
{
    private $ingredientService;

    public function __construct(IngredientService $ingredientService)
    {
        $this->ingredientService = $ingredientService;
    }


    #[Route('/ingredients', name: 'create', methods: ['POST'])]
    public function createIngredientsAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonResponse(['error' => 'No data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $ingredientCollection = new IngredientCollection();

        foreach ($data as $ingredientData) {
            $ingredient = new Ingredient();
            $ingredient->setName($ingredientData['name']);
            $ingredient->setUnit($ingredientData['unit']);
            $ingredient->setProteins($ingredientData['proteins']);
            $ingredient->setFat($ingredientData['fat']);
            $ingredient->setCarbs($ingredientData['carbs']);
            $ingredient->setCalories($ingredientData['calories']);

            $ingredientCollection->addIngredient($ingredient);
        }
        try {
            $result = $this->ingredientService->createIngredients($ingredientCollection);

            return new JsonResponse([
                'message' => 'Ingredient creation result',
                'created' => array_map(fn($i) => $i->getName(), $result['created']->getIngredients()),
                'existing' => array_map(fn($i) => $i->getName(), $result['existing']->getIngredients()),

            ], count($result['created']) > 0 ? JsonResponse::HTTP_CREATED : JsonResponse::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/ingredients', name: 'getIngredientsList', methods: ['GET'])]
    public function getIngredientsListAction(): JsonResponse
    {
        try {
            // Récupérer tous les ingrédients
            $ingredientsCollection = $this->ingredientService->getIngredientsList();

            // Préparer les données pour la réponse
            $collection = [];
            foreach ($ingredientsCollection as $ingredient) {
                $collection[] = [
                    'name' => $ingredient->getName(),
                    'unit' => $ingredient->getUnit(),
                    'proteins' => $ingredient->getProteins(),
                    'fat' => $ingredient->getFat(),
                    'carbs' => $ingredient->getCarbs(),
                    'calories' => $ingredient->getCalories(),
                ];
            }

            // Retourner les données sous forme de réponse JSON
            return new JsonResponse($collection);
        } catch (\Exception $e) {
            // Gestion d'erreur en cas de problème
            return new JsonResponse(['error' => 'Failed to retrieve ingredients: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/ingredients/{name}', name: 'getIngredientByName', methods: ['GET'])]
    public function getIngredientsByNameAction(string $name): JsonResponse
    {
        $name = ucfirst($name);

        try {
            // Récupérer les ingrédients correspondant partiellement au name
            $ingredientsCollection = $this->ingredientService->getIngredientsByName($name);

            if ($ingredientsCollection->isEmpty()) {
                return new JsonResponse(['error' => 'No ingredients found'], JsonResponse::HTTP_NOT_FOUND);
            }

            // Transformer la collection en tableau de données JSON
            $data = [];

            foreach ($ingredientsCollection->getIngredients() as $ingredient) {
                $data[] = [
                    'name' => $ingredient->getName(),
                    'unit' => $ingredient->getUnit(),
                    'proteins' => $ingredient->getProteins(),
                    'fat' => $ingredient->getFat(),
                    'carbs' => $ingredient->getCarbs(),
                    'calories' => $ingredient->getCalories(),
                ];
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to retrieve ingredients: ' . $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/ingredients/{name}', name: 'delete', methods: ['DELETE'])]
    public function deleteIngredientByNameAction(string $name): JsonResponse
    {
        if (empty($name)) {
            return new JsonResponse(['error' => 'Ingredient name is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $name = ucfirst($name);
            $ingredientCollection = $this->ingredientService->getIngredientsByName($name);

            if ($ingredientCollection->isEmpty()) {
                return new JsonResponse(['error' => 'No matching ingredients found'], Response::HTTP_NOT_FOUND);
            }

            $this->ingredientService->deleteIngredients($ingredientCollection);

            return new JsonResponse(['message' => 'Matching ingredients deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to delete ingredients: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/ingredients', name: 'update', methods: ['PUT'])]
    public function updateIngredientsAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonResponse(['error' => 'No data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $ingredientCollection = new IngredientCollection();

        foreach ($data as $ingredientData) {
            $ingredient = new Ingredient();
            $ingredient->setName($ingredientData['name']);
            $ingredient->setUnit($ingredientData['unit']);
            $ingredient->setProteins($ingredientData['proteins']);
            $ingredient->setFat($ingredientData['fat']);
            $ingredient->setCarbs($ingredientData['carbs']);
            $ingredient->setCalories($ingredientData['calories']);

            $ingredientCollection->addIngredient($ingredient);
        }

        try {
            $result = $this->ingredientService->updateIngredients($ingredientCollection);

            // Préparer les suggestions pour les ingrédients non trouvés
            $suggestions = [];
            $notFoundNames = array_map(fn(Ingredient $i) => $i->getName(), $result['not_found']->getIngredients());

            foreach ($ingredientCollection as $ingredient) {
                if (in_array($ingredient->getName(), $notFoundNames, true)) {
                    $suggestions[] = [
                        'message' => 'Ingredient not found. Would you like to create it?',
                        'ingredient' => [
                            'name' => $ingredient->getName(),
                            'unit' => $ingredient->getUnit(),
                            'proteins' => $ingredient->getProteins(),
                            'fat' => $ingredient->getFat(),
                            'carbs' => $ingredient->getCarbs(),
                            'calories' => $ingredient->getCalories()
                        ]
                    ];
                }
            }

            $statusCode = JsonResponse::HTTP_OK;
            $message = 'Ingredient update result';
            $updatedNames = array_map(fn(Ingredient $i) => $i->getName(),$result['updated']->getIngredients());

            if (count($updatedNames) === 0 && count($notFoundNames) > 0) {
                $message = 'No ingredients were updated. Some ingredients were not found.';
            }

            return new JsonResponse([
                'message' => $message,
                'updated' => $updatedNames,
                'not_found' => $notFoundNames,
                'suggestions' => $suggestions
            ], $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
