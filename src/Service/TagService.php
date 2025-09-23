<?php

namespace App\Service;

use App\Entity\Tag;
use App\Entity\TagCollection;
use App\Interface\TagServiceInterface;
use App\Repository\TagRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagService implements TagServiceInterface
{
    private TagRepository $repository;

    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crée de nouvelles entités {{ entityClass|basename }} à partir d'une collection.
     *
     * @return array{created: TagCollection, existing: TagCollection}
     */
    public function createTagCollection(TagCollection $tagCollection): array
    {
        $newTagCollection = new TagCollection();
        $existing = new TagCollection();

        foreach ($tagCollection->getTags() as $tag) {
            if ($this->checkIfExists($tag)) {
                $existing->addTag($tag);
            } else {
                $newTagCollection->addTag($tag);
                $this->repository->createTag($tag);
            }
        }

        return [
            'created' => $newTagCollection,
            'existing' => $existing,
        ];
    }

    public function update(Tag $tag): void
    {
        exit;
    }

    /**
     * Supprime une entité Tag par son ID.
     *
     * @throws NotFoundHttpException Si l'entité n'existe pas
     */
    public function deleteTagById(int $id): void
    {
        $entity = $this->repository->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('%s with id %d not found.', $id, $entity));
        }
        $this->repository->deleteTag($entity);
    }

    public function find(int $id): ?Tag
    {
        return $this->repository->find($id);
    }

    /**
     * Vérifie si une entité existe déjà en base en fonction d'un champ unique.
     *
     * @param Tag $tag @return bool
     */
    private function checkIfExists(Tag $tag): bool
    {
        // Ici, on suppose que la propriété "name" est unique (adapter si besoin)
        $existingTag = $this->repository->findOneBy([
            'name' => $tag->getName(),
        ]);

        return null !== $existingTag;
    }
}
