<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use App\Interface\IngredientServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController extends AbstractController
{
    private IngredientServiceInterface $ingredientService;
    public const BASE_PATH = '/ingredients';
    public const RETREIVE_FAILED = 'Failed to retrieve ingredients: ';

    public function __construct(IngredientServiceInterface $ingredientService)
    {
        $this->ingredientService = $ingredientService;
    }


    /**
     * Crée une collection d'ingrédients à partir du payload JSON.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(self::BASE_PATH .'/create', name: 'createIngredients', methods: ['POST'])]
    public function createIngredientsAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonResponse(['error' => 'No data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $ingredientCollection = new IngredientCollection();

        foreach ($data as $ingredientData) {
            $ingredient = new Ingredient();
            $ingredient->setName(ucfirst($ingredientData['name']));
            $ingredient->setUnit($ingredientData['unit']);
            $ingredient->setProteins($ingredientData['proteins']);
            $ingredient->setFat($ingredientData['fat']);
            $ingredient->setCarbs($ingredientData['carbs']);
            $ingredient->setCalories($ingredientData['calories']);

            $ingredientCollection->addIngredient($ingredient);
        }
        try {
            $result = $this->ingredientService->createIngredients($ingredientCollection);

            $createdNames = array_map(fn($i) => $i->getName(), $result['created']->getIngredients());
            $existingNames = array_map(fn($i) => $i->getName(), $result['existing']->getIngredients());

            $isConflict = count($createdNames) === 0;

            return new JsonResponse([
                'message' => $isConflict
                    ? 'No ingredient created: all provided ingredients already exist (conflict).'
                    : 'Ingredient creation result.',
                'created' => $createdNames,
                'existing' => $existingNames,
            ], $isConflict ? JsonResponse::HTTP_CONFLICT : JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Retourne la liste complète des ingrédients.
     *
     * @return JsonResponse
     */
    #[Route(self::BASE_PATH, name: 'getIngredientsList', methods: ['GET'])]
    public function getIngredientsListAction(): JsonResponse
    {
        try {
            // Récupérer tous les ingrédients
            $ingredientsCollection = $this->ingredientService->getIngredientsList();

            // Préparer les données pour la réponse
            $collection = [];
            foreach ($ingredientsCollection as $ingredient) {
                $collection[] = [
                    'id' => $ingredient->getId(),
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
            return new JsonResponse(['error' => self::RETREIVE_FAILED . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retourne un ingrédient unique par son nom.
     *
     * @param string $name
     * @return JsonResponse
     */
    #[Route(self::BASE_PATH . '/single/{name}', name: 'getSingleIngredientByName', methods: ['GET'])]
    public function getSingleIngredientsByNameAction(string $name): JsonResponse
    {
        $name = ucfirst($name);
        try {
            $data = [];
            $ingredient = $this->ingredientService->findOneByName($name);

            if (!$ingredient) {
                return new JsonResponse(['error' => 'No ingredients found'], JsonResponse::HTTP_NOT_FOUND);
            }
            $data[] = [
                'id' => $ingredient->getId(),
                'name' => $ingredient->getName(),
                'unit' => $ingredient->getUnit(),
                'proteins' => $ingredient->getProteins(),
                'fat' => $ingredient->getFat(),
                'carbs' => $ingredient->getCarbs(),
                'calories' => $ingredient->getCalories(),
            ];
            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => self::RETREIVE_FAILED . $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retourne plusieurs ingrédients correspondant partiellement au nom.
     *
     * @param string $name
     * @return JsonResponse
     */
    #[Route(self::BASE_PATH . '/{name}', name: 'getMultipleIngredientByName', methods: ['GET'])]
    public function getMultipleIngredientsByNameAction(string $name): JsonResponse
    {
        $name = ucfirst($name);

        try {
            // Récupérer les ingrédients correspondant partiellement au name
            $ingredientsCollection = $this->ingredientService->getMultipleIngredientsByName($name);

            // Transformer la collection en tableau de données JSON
            $data = [];

            foreach ($ingredientsCollection->getIngredients() as $ingredient) {
                $data[] = [
                    'id' => $ingredient->getId(),
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
                ['error' => self::RETREIVE_FAILED . $e->getMessage()],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Supprime un ingrédient unique par son id.
     *
     * @param int $id
     * @return JsonResponse
     */
    #[Route(self::BASE_PATH . '/single/{id}', name: 'deleteSingleIngredientById', methods: ['DELETE'])]
    public function deleteSingleIngredientByIdAction(int $id): JsonResponse
    {
        $response = null;
        $ingredient = $this->ingredientService->findOneById($id);

        if (!$ingredient) {
            return new JsonResponse(['error' => 'No matching ingredients found'], Response::HTTP_NOT_FOUND);
        } else {
            try {
                $ingredientData = [
                    'id' => $ingredient->getId(),
                    'name' => $ingredient->getName(),
                ];

                $this->ingredientService->deleteSingleIngredientById($ingredient);

                $response = new JsonResponse([
                    'message' => 'Ingredient deleted successfully',
                    'ingredient' => $ingredientData,
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                $response = new JsonResponse([
                    'error' => 'Failed to delete ingredient: ' . $e->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $response;
    }

    /**
     * Supprime tous les ingrédients correspondant au nom fourni.
     * if {name} is not provided, the RouterListener will raise a 404
     *
     * @param string $name
     * @return JsonResponse
     */
    #[Route(self::BASE_PATH . '/{name}', name: 'deleteMultipleIngredientsByName', methods: ['DELETE'])]
    public function deleteMultipleIngredientsByNameAction(string $name): JsonResponse
    {
        $response = null;
        try {
            $name = ucfirst($name);
            $ingredientCollection = $this->ingredientService->getMultipleIngredientsByName($name);

            if ($ingredientCollection->isEmpty()) {
                return new JsonResponse(['error' => 'No matching ingredients found'], Response::HTTP_NOT_FOUND);
            }

            $this->ingredientService->deleteMultipleIngredients($ingredientCollection);
            $response = new JsonResponse(['message' => 'Matching ingredients deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $response = new JsonResponse(['error' => 'Failed to delete ingredients: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $response;
    }

    /**
     * Met à jour une collection d'ingrédients depuis le payload JSON.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(self::BASE_PATH, name: 'update', methods: ['PUT'])]
    public function updateIngredientsAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return new JsonResponse(['error' => 'No data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Création d'une collection à partir du payload
        $ingredientCollection = new IngredientCollection();

        foreach ($data as $ingredientData) {
            $ingredient = new Ingredient();

            if (isset($ingredientData['id'])) {
                $ingredient->setId($ingredientData['id']); // Setter temporaire uniquement ici, Doctrine gère le create
            }

            $ingredient
                ->setName($ingredientData['name'])
                ->setUnit($ingredientData['unit'])
                ->setProteins($ingredientData['proteins'])
                ->setFat($ingredientData['fat'])
                ->setCarbs($ingredientData['carbs'])
                ->setCalories($ingredientData['calories']);

            $ingredientCollection->addIngredient($ingredient);
        }

        try {
            $result = $this->ingredientService->updateIngredients($ingredientCollection);

            return new JsonResponse([
                'message' => count($result['updated']) > 0 ? 'Ingredients updated' : 'No ingredients were updated.',
                'updated' => array_map(fn(Ingredient $i) => $i->getName(), $result['updated']->getIngredients()),
                'not_found' => array_map(fn(Ingredient $i) => $i->getName(), $result['not_found']->getIngredients()),
                'suggestions' => array_map(function (Ingredient $i) {
                    return [
                        'message' => 'Ingredient not found. Would you like to create it?',
                        'ingredient' => [
                            'name' => $i->getName(),
                            'unit' => $i->getUnit(),
                            'proteins' => $i->getProteins(),
                            'fat' => $i->getFat(),
                            'carbs' => $i->getCarbs(),
                            'calories' => $i->getCalories()
                        ]
                    ];
                }, $result['not_found']->getIngredients())
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
