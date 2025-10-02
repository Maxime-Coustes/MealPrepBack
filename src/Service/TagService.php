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
            $getter = 'get' . ucfirst($column);
            $setter = 'set' . ucfirst($column);

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
     * Met à jour une collection d'entités Tag en appliquant les nouvelles valeurs fournies.
     *
     * Chaque entité de la collection est comparée à l'entité existante en base.
     * Si l'entité existe, les valeurs de ses propriétés sont mises à jour dynamiquement
     * via applyNewValues / setFieldIfExists. Les entités mises à jour sont renvoyées,
     * ainsi que celles non trouvées en base.
     *
     * @return array{
     *     updated: TagCollection,   // Collection des entités mises à jour
     *     not_found: TagCollection  // Collection des entités non trouvées en base
     * }
     */
    public function updateTags(TagCollection $tagCollection): array
    {
        $toUpdate = new TagCollection();
        $notFound = new TagCollection();

        foreach ($tagCollection->getTags() as $tag) {
            $existing = $this->repository->find($tag->getId());

            if (!$existing) {
                $notFound->addTag($tag);

                continue;
            }

            $this->applyNewValues($existing, $tag, $toUpdate);
        }

        $this->repository->updateTags($toUpdate);

        return [
            'updated' => $toUpdate,
            'not_found' => $notFound,
        ];
    }

    /**
     * Applique les nouvelles valeurs d'un payload sur l'entité existante.
     *
     * Parcourt toutes les clés du tableau __newValues (placeholder temporaire) du payload
     * et appelle setFieldIfExists pour chaque champ.
     *
     * @param object        $existing L'entité existante en base
     * @param Tag           $payload  L'entité contenant les nouvelles valeurs
     * @param TagCollection $toUpdate La collection où ajouter les entités modifiées
     */
    private function applyNewValues($existing, $payload, TagCollection $toUpdate): void
    {
        $newValues = $payload->__newValues ?? [];

        foreach ($newValues as $field => $value) {
            $this->setFieldIfExists($existing, $field, $value, $toUpdate);
        }
    }

    /**
     * Met à jour une propriété d'une entité si celle-ci existe et que sa valeur est différente.
     *
     * Vérifie l'existence de la propriété, du getter et du setter correspondants avant
     * de modifier la valeur. Ajoute l'entité à la collection des entités mises à jour si nécessaire.
     *
     * @param object        $entity   L'entité à modifier
     * @param string        $field    Le nom du champ à mettre à jour
     * @param mixed         $newValue La nouvelle valeur à appliquer
     * @param TagCollection $toUpdate La collection où ajouter l'entité si modifiée
     */
    private function setFieldIfExists($entity, string $field, $newValue, TagCollection $toUpdate): void
    {
        if (!property_exists($entity, $field)) {
            return;
        }

        $getter = 'get' . ucfirst($field);
        $setter = 'set' . ucfirst($field);

        if (!method_exists($entity, $getter) || !method_exists($entity, $setter)) {
            return;
        }

        $oldValue = $entity->$getter();

        if ($oldValue !== $newValue) {
            $entity->$setter($newValue);
            $toUpdate->addTag($entity);
        }
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
