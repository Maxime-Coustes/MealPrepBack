<?php

namespace App\Tests\Controller;

use App\Controller\IngredientController;
use App\Entity\Ingredient;
use App\Entity\IngredientCollection;
use App\Interface\IngredientServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class IngredientControllerTest extends TestCase
{
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
            '/ingredients',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
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
}
