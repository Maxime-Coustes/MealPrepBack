<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\TagCollection;
use Doctrine\Persistence\ManagerRegistry;


class TagRepository extends AbstractSolidRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }
/**
     * Persiste un Tag unique.
     */
    public function createTag(Tag $tag): void
    {
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();
    }

    /**
     * Persiste plusieurs Tags à partir d'une TagCollection.
     */
    public function createTags(TagCollection $tags): void
    {
        foreach ($tags->getTags() as $tag) {
            $this->getEntityManager()->persist($tag);
        }
        $this->getEntityManager()->flush();
    }

        public function getEntityClass(): string
    {
        return Tag::class;
    }

    /**
     * Met à jour une collection de Tags.
     */
    public function updateTags(TagCollection $tags): void
    {
        foreach ($tags->getTags() as $tag) {
            $this->getEntityManager()->persist($tag);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Supprime un Tag.
     */
    public function deleteTag(Tag $tag): void
    {
        $this->getEntityManager()->remove($tag);
        $this->getEntityManager()->flush();
    }


    /**
     * Cherche un Tag par son nom.
     */
    public function findOneByName(string $name): ?Tag
    {
        return $this->findOneBy(['name' => $name]);
    }
}
