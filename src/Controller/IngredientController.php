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
            // Appeler le service une seule fois et récupérer le résultat
            $success = $this->ingredientService->createIngredients($ingredientCollection);

            // Gérer le résultat en fonction de la réponse
            if ($success) {
                $names = array_column($data, 'name');
                return new JsonResponse(['message' => 'Ingredients created successfully', 'ingredients' => $names], JsonResponse::HTTP_CREATED);
            } else {
                $names = array_column($data, 'name');
                return new JsonResponse(['message' => 'Ingredient already exists', 'ingredients' => $names], JsonResponse::HTTP_CONFLICT);
            }
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

        // Vérifie si les données sont vides
        if (empty($data)) {
            return new JsonResponse(['error' => 'No data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Crée une collection d'ingrédients à partir des données
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
            $updatedIngredients = $this->ingredientService->updateIngredients($ingredientCollection);
            return new JsonResponse([
                'message' => 'Ingredients updated successfully',
                'ingredients' => $updatedIngredients
            ], JsonResponse::HTTP_OK);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse([
                'status' => 404,
                'error' => $e->getMessage(),
                'suggestions' => ['Would you like to create it?']
            ], 404);
        }
    }
}
