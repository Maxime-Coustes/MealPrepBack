<?php

namespace App\Service;

use App\Entity\Tag;
use App\Entity\TagCollection;
use App\Interface\TagServiceInterface;
use App\Repository\TagRepository;
use Src\Utils\DoctrineHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagService implements TagServiceInterface
{
    private TagRepository $repository;

    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crée de nouvelles entités Tag à partir d'une collection.
     *
     * @return array{created: TagCollection, existing: TagCollection}
     */
    public function createTagCollection(TagCollection $tagCollection): array
    {
        $columns = DoctrineHelper::getDoctrineColumns($this->repository->getEntityClass());

        $newTagCollection = new TagCollection();
        $existing = new TagCollection();

        foreach ($tagCollection->getTags() as $tag) {
            // Appliquer les règles génériques (ex: normalisation du nom)
            $this->applyGenericRules($tag, $columns);

            if ($this->checkIfExists($tag)) {
                $tag = $this->repository->findOneBy([
                    'name' => $tag->getName(),
                ]);
                $existing->addTag($tag);
            } else {
                $newTagCollection->addTag($tag);
            }
        }

        if (!$newTagCollection->isEmpty()) {
            $this->repository->createTags($newTagCollection);
        }

        return [
            'created' => $newTagCollection,
            'existing' => $existing,
        ];
    }

    /**
     * Applique des règles de normalisation sur les propriétés de l'entité Tag    * en fonction des colonnes connues de Doctrine.
     *
     * Exemple actuel :
     * - "name" : force la casse à "Majuscule + minuscules".
     *
     * @param Tag $tag * @param string[] $columns
     */
    private function applyGenericRules(Tag $tag, array $columns): void
    {
        foreach ($columns as $column) {
            $getter = 'get'.ucfirst($column);
            $setter = 'set'.ucfirst($column);

            if (!method_exists($tag, $getter) || !method_exists($tag, $setter)) {
                continue;
            }

            $value = $tag->$getter();

            if ('name' === $column && null !== $value) {
                $value = ucfirst(strtolower($value));
            }

            $tag->$setter($value);
        }
    }

    /**
     * Met à jour une collection d'entités Tag.
     *
     * @return array{updated: TagCollection, not_found: TagCollection}
     */
    public function updateTags(TagCollection $tagCollection): array
    {
        $toUpdate = new TagCollection();
        $notFound = new TagCollection();

        $em = $this->repository->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Récupération dynamique des propriétés simples via Reflection
        $columns = [];
        $reflection = new \ReflectionClass($this->repository->getEntityClass());

        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(\Doctrine\ORM\Mapping\Column::class);

            if (!empty($attrs)) {
                $columns[] = $property->getName();
            }
        }

        foreach ($tagCollection->getTags() as $tag) {
            $id = $tag->getId();

            if (null === $id) {
                $notFound->addTag($tag);

                continue;
            }

            $existing = $this->repository->find($id);

            if (!$existing) {
                $notFound->addTag($tag);

                continue;
            }

            // Snapshot original Doctrine (avant modification)
            $orig = $uow->getOriginalEntityData($existing);

            $hasChanged = false;

            foreach ($columns as $column) {
                $getter = 'get'.ucfirst($column);
                $setter = 'set'.ucfirst($column);

                $newValue = $tag->$getter();
                $oldValue = $orig[$column] ?? $existing->$getter();

                if ($oldValue !== $newValue) {
                    $existing->$setter($newValue);
                    $hasChanged = true;
                }
            }

            if ($hasChanged) {
                $toUpdate->addTag($existing);
            }
        }

        $this->repository->updateTags($toUpdate);

        return [
            'updated' => $toUpdate,
            'not_found' => $notFound,
        ];
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
            throw new NotFoundHttpException(sprintf(' Tag with id %d not found.', $id));
        }
        $this->repository->deleteTag($entity);
    }

    public function find(int $id): ?Tag
    {
        return $this->repository->find($id);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
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
