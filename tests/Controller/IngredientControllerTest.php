<?php

namespace App\Tests\Controller;

use App\Controller\IngredientController;
use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use App\Interface\IngredientServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IngredientControllerTest extends TestCase
{
    public const BASE_PATH = '/ingredients';
    public const BASE_FORMAT = 'application/json';

    public function testCreateIngredientsActionReturnsCreatedResponse(): void
    {
        $postData = [
            [
                'name' => 'tomate',
                'unit' => 'g',
                'proteins' => 1.0,
                'fat' => 0.2,
                'carbs' => 3.5,
                'calories' => 20,
            ]
        ];

        $jsonContent = json_encode($postData);
        $this->assertNotFalse($jsonContent, 'json_encode failed');

        $request = Request::create(
            self::BASE_PATH,
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => self::BASE_FORMAT],
            $jsonContent
        );

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);

        $createdCollection = new IngredientCollection();
        $existingCollection = new IngredientCollection();

        $ingredient = new Ingredient();
        $ingredient->setName('Tomate');
        $createdCollection->addIngredient($ingredient);

        $ingredientServiceMock->method('createIngredients')
            ->willReturn([
                'created' => $createdCollection,
                'existing' => $existingCollection,
            ]);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->createIngredientsAction($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $data = json_decode($content, true);

        $this->assertIsArray($data);

        $this->assertEquals(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals(['Tomate'], $data['created']);
        $this->assertEmpty($data['existing']);
        $this->assertStringContainsString('Ingredient creation result', $data['message']);
    }

    public function testGetIngredientsListActionReturnsList(): void
    {
        $ingredient = new Ingredient();
        $ingredient->setName('Tomate');
        $ingredient->setUnit('g');
        $ingredient->setProteins(1.0);
        $ingredient->setFat(0.2);
        $ingredient->setCarbs(3.5);
        $ingredient->setCalories(20);

        $ingredientsCollection = new IngredientCollection();
        $ingredientsCollection->addIngredient($ingredient);

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('getIngredientsList')->willReturn($ingredientsCollection);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->getIngredientsListAction();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $this->decodeResponseContent($response);
        $this->assertCount(1, $data);
        $this->assertEquals('Tomate', $data[0]['name']);
        $this->assertEquals('g', $data[0]['unit']);
        $this->assertEquals(1.0, $data[0]['proteins']);
    }

    public function testGetSingleIngredientsByNameActionReturnsIngredient(): void
    {
        $ingredient = new Ingredient();
        $ingredient->setName('Tomate');
        $ingredient->setUnit('g');
        $ingredient->setProteins(1.0);
        $ingredient->setFat(0.2);
        $ingredient->setCarbs(3.5);
        $ingredient->setCalories(20);
        $ingredient->setId(123);

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('findOneByName')->with('Tomate')->willReturn($ingredient);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->getSingleIngredientsByNameAction('tomate');

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $this->decodeResponseContent($response);
        $this->assertCount(1, $data);
        $this->assertEquals(123, $data[0]['id']);
        $this->assertEquals('Tomate', $data[0]['name']);
    }

    public function testGetSingleIngredientsByNameActionReturnsNotFound(): void
    {
        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('findOneByName')->with('NonExistent')->willReturn(null);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->getSingleIngredientsByNameAction('NonExistent');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = $this->decodeResponseContent($response);
        $this->assertArrayHasKey('error', $data);
    }

    public function testGetMultipleIngredientsByNameActionReturnsList(): void
    {
        $ingredient1 = new Ingredient();
        $ingredient1->setName('Tomate');
        $ingredient1->setId(1);
        $ingredient2 = new Ingredient();
        $ingredient2->setName('Tomatillo');
        $ingredient2->setId(2);

        $collection = new IngredientCollection();
        $collection->addIngredient($ingredient1);
        $collection->addIngredient($ingredient2);

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('getMultipleIngredientsByName')->with('Tom')->willReturn($collection);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->getMultipleIngredientsByNameAction('tom');

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $this->decodeResponseContent($response);
        $this->assertCount(2, $data);
        $this->assertEquals('Tomate', $data[0]['name']);
        $this->assertEquals('Tomatillo', $data[1]['name']);
    }

    public function testDeleteSingleIngredientByIdActionReturnsSuccess(): void
    {
        $ingredient = new Ingredient();
        $ingredient->setId(5);
        $ingredient->setName('Tomate');

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('findOneById')->with(5)->willReturn($ingredient);
        $ingredientServiceMock->expects($this->once())->method('deleteSingleIngredientById')->with($ingredient);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->deleteSingleIngredientByIdAction(5);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->decodeResponseContent($response);
        $this->assertEquals('Ingredient deleted successfully', $data['message']);
        $this->assertEquals('Tomate', $data['ingredient']['name']);
    }

    public function testDeleteSingleIngredientByIdActionReturnsNotFound(): void
    {
        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('findOneById')->with(999)->willReturn(null);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->deleteSingleIngredientByIdAction(999);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = $this->decodeResponseContent($response);
        $this->assertArrayHasKey('error', $data);
    }

    public function testDeleteMultipleIngredientsByNameActionReturnsSuccess(): void
    {
        $ingredient1 = new Ingredient();
        $ingredient1->setName('Tomate');

        $collection = new IngredientCollection();
        $collection->addIngredient($ingredient1);

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('getMultipleIngredientsByName')->with('Tomate')->willReturn($collection);
        $ingredientServiceMock->expects($this->once())->method('deleteMultipleIngredients')->with($collection);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->deleteMultipleIngredientsByNameAction('tomate');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->decodeResponseContent($response);
        $this->assertEquals('Matching ingredients deleted successfully', $data['message']);
    }

    public function testDeleteMultipleIngredientsByNameActionReturnsNotFound(): void
    {
        $emptyCollection = new IngredientCollection();

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('getMultipleIngredientsByName')->with('Unknown')->willReturn($emptyCollection);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->deleteMultipleIngredientsByNameAction('Unknown');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = $this->decodeResponseContent($response);
        $this->assertArrayHasKey('error', $data);
    }

    public function testUpdateIngredientsActionReturnsUpdated(): void
    {
        $postData = [
            [
                'id' => 1,
                'name' => 'Tomate',
                'unit' => 'g',
                'proteins' => 1.0,
                'fat' => 0.2,
                'carbs' => 3.5,
                'calories' => 20,
            ]
        ];

        $jsonContent = json_encode($postData);
        $this->assertNotFalse($jsonContent);

        $request = Request::create(
            self::BASE_PATH,
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => self::BASE_FORMAT],
            $jsonContent
        );

        $ingredient = new Ingredient();
        $ingredient->setName('Tomate');

        $updatedCollection = new IngredientCollection();
        $updatedCollection->addIngredient($ingredient);

        $notFoundCollection = new IngredientCollection();

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $ingredientServiceMock->method('updateIngredients')->willReturn([
            'updated' => $updatedCollection,
            'not_found' => $notFoundCollection,
        ]);

        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->updateIngredientsAction($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->decodeResponseContent($response);

        $this->assertEquals('Ingredients updated', $data['message']);
        $this->assertContains('Tomate', $data['updated']);
        $this->assertEmpty($data['not_found']);
    }

    public function testUpdateIngredientsActionReturnsBadRequestOnEmptyData(): void
    {
        //phpStan requirement
        $content = json_encode([]);
        if ($content === false) {
            $content = ''; // ou throw une exception selon ta logique
        }
        $request = Request::create(
            self::BASE_PATH,
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => self::BASE_FORMAT],
            $content
        );

        $ingredientServiceMock = $this->createMock(IngredientServiceInterface::class);
        $controller = new IngredientController($ingredientServiceMock);

        $response = $controller->updateIngredientsAction($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = $this->decodeResponseContent($response);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * @param JsonResponse $response
     * @return array<string|int, mixed>
     */
    private function decodeResponseContent(JsonResponse $response): array
    {
        $content = $response->getContent();
        $this->assertNotFalse($content, 'La réponse ne doit pas être vide');

        $data = json_decode($content, true);
        $this->assertIsArray($data, 'Le contenu JSON doit être décodable en tableau');

        return $data;
    }
}
