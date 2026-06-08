<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

final class RecipeControllerTest extends KernelTestCase
{
    public function testIndex(): void
    {
        self::bootKernel();

        $router = static::getContainer()->get(RouterInterface::class);
        self::assertInstanceOf(RouterInterface::class, $router);

        $route = $router->match('/hello');

        self::assertSame('app_recipe', $route['_route']);
    }
}
