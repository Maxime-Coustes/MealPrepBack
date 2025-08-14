<?php

namespace App\Service;

use App\Interface\RecipeServiceInterface;
use App\Repository\RecipeRepository;

class RecipeService implements RecipeServiceInterface
{
    private RecipeRepository $repository;

    public function __construct(RecipeRepository $repository)
    {
        $this->repository = $repository;
    }

    // Implémentation des méthodes
}
