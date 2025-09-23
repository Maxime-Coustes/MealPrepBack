<?php

namespace App\Interface;

use App\Entity\Tag;
use App\Entity\TagCollection;

interface TagServiceInterface
{
    /**
     * Crée de nouvelles entités Tag à partir d'une collection.
     *
     * @return array{created: TagCollection ?>, existing: TagCollection ?>}
     */
    public function createTagCollection(TagCollection $tagCollection): array;

    /**
     * Met à jour une collection d'entités Tag.
     *
     * @return array{updated: TagCollection, not_found: TagCollection}
     */
    public function updateTags(TagCollection $tagCollection): array;

    /**
     * Supprime une entité Tag par son ID.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si l'entité n'existe pas
     */
    public function deleteTagById(int $id): void;

    /**
     * Recherche une entité Tag par son ID.
     */
    public function find(int $id): ?Tag;
}
