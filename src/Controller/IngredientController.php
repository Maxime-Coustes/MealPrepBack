<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use App\Service\IngredientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
            $ingredient->setNom($ingredientData['nom']);
            $ingredient->setUnite($ingredientData['unite']);
            $ingredient->setProteines($ingredientData['proteines']);
            $ingredient->setLipides($ingredientData['lipides']);
            $ingredient->setGlucides($ingredientData['glucides']);
            $ingredient->setCalories($ingredientData['calories']);

            $ingredientCollection->addIngredient($ingredient);
        }

        try {
            // Appeler le service une seule fois et récupérer le résultat
            $success = $this->ingredientService->createIngredients($ingredientCollection);

            // Gérer le résultat en fonction de la réponse
            if ($success) {
                $noms = array_column($data, 'nom');
                return new JsonResponse(['message' => 'Ingredients created successfully', 'ingredients' => $noms], JsonResponse::HTTP_CREATED);
            } else {
                $noms = array_column($data, 'nom');
                return new JsonResponse(['message' => 'Ingredient already exists', 'ingredients' => $noms], JsonResponse::HTTP_CONFLICT);
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
            $ingredientsCollection = $this->ingredientService->getIngredientsListAction();

            // Préparer les données pour la réponse
            $collection = [];
            foreach ($ingredientsCollection as $ingredient) {
                $collection[] = [
                    'nom' => $ingredient->getNom(),
                    'unite' => $ingredient->getUnite(),
                    'proteines' => $ingredient->getProteines(),
                    'lipides' => $ingredient->getLipides(),
                    'glucides' => $ingredient->getGlucides(),
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

    #[Route('/ingredients/{nom}', name: 'getIngredientByName', methods: ['GET'])]
    public function getIngredientByNameAction(string $nom): JsonResponse
    {
        $nom = ucfirst($nom);
        try {
            // Récupérer l'ingrédient par son nom
            $ingredient = $this->ingredientService->getIngredientByName($nom);

            if (!$ingredient) {
                return new JsonResponse(['error' => 'Ingredient not found'], JsonResponse::HTTP_NOT_FOUND);
            }

            // Retourner les données sous forme de réponse JSON
            $data = [
                'nom' => $ingredient->getNom(),
                'unite' => $ingredient->getUnite(),
                'proteines' => $ingredient->getProteines(),
                'lipides' => $ingredient->getLipides(),
                'glucides' => $ingredient->getGlucides(),
                'calories' => $ingredient->getCalories(),
            ];

            return new JsonResponse($data);
        } catch (\Exception $e) {
            // Gestion d'erreur en cas de problème
            return new JsonResponse(['error' => 'Failed to retrieve ingredient: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/ingredients/{nom}', name: 'delete', methods: ['DELETE'])]
    public function deleteIngredientByNameAction(string $nom): JsonResponse
    {
        // Vérifier si le nom de l'ingrédient est fourni
        if (empty($nom)) {
            return new JsonResponse(['error' => 'Ingredient name is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Récupérer l'ingrédient à partir de son nom
            $ingredient = $this->ingredientService->getIngredientByName($nom);

            // Si l'ingrédient n'existe pas, retourner une erreur
            if (!$ingredient) {
                return new JsonResponse(['error' => 'Ingredient not found'], Response::HTTP_NOT_FOUND);
            }

            // Si l'ingrédient existe, le supprimer
            $this->ingredientService->deleteIngredient($ingredient);

            // Retourner une réponse de succès
            return new JsonResponse(['message' => 'Ingredient deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Gestion des erreurs et retour d'une réponse d'erreur générique
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
