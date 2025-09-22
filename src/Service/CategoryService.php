<?php

namespace App\Service;

use App\Entity\Category;
use App\Interface\CategoryServiceInterface;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryService implements CategoryServiceInterface
{
    private CategoryRepository $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    // Exemple de méthodes avec l’entité
    public function create(Category $category): void
    {
        exit;
    }

    public function update(Category $category): void
    {
        exit;
    }

    /**
     * Supprime une entité Category par son ID.
     *
     * @throws NotFoundHttpException Si l'entité n'existe pas
     */
    public function deleteCategoryById(int $id): void
    {
        $entity = $this->repository->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('%s with id %d not found.', $id, $entity));
        }
        $this->repository->deleteCategory($entity);
    }

    public function find(int $id): ?Category
    {
        return $this->repository->find($id);
    }
}
