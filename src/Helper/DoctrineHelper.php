<?php

namespace Src\Utils;

class DoctrineHelper
{

    /**
     * Retourne la liste des colonnes Doctrine d'une entité donnée.
     *
     * Utilise la reflection pour récupérer toutes les propriétés annotées
     * avec `#[ORM\Column]`. Permet d'exclure la colonne `id` si nécessaire.
     * Les résultats sont mis en cache statiquement pour éviter de recalculer
     * plusieurs fois les colonnes pour la même classe.
     *
     * @param class-string $entityClass Le nom complet de la classe de l'entité
     * @param bool $excludeId Indique si la colonne "id" doit être exclue (par défaut true)
     * @return string[] Tableau contenant les noms des colonnes Doctrine
     */
    public static function getDoctrineColumns(string $entityClass, bool $excludeId = true): array
    {
        static $columnsCache = [];

        if (isset($columnsCache[$entityClass])) {
            return $columnsCache[$entityClass];
        }

        $columns = [];
        $reflection = new \ReflectionClass($entityClass);

        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(\Doctrine\ORM\Mapping\Column::class);
            if (!empty($attrs) && (!$excludeId || $property->getName() !== 'id')) {
                $columns[] = $property->getName();
            }
        }

        $columnsCache[$entityClass] = $columns;

        return $columns;
    }

    /**
     * Remplit les propriétés d'une entité à partir d'un tableau.
     *
     * @template T
     * @param class-string<T>|T $entityOrClass Instance ou nom de la classe de l'entité
     * @param array<string, mixed> $data Tableau associatif [champ => valeur]
     * @param bool $useDoctrineColumns Si true, ne prend que les colonnes Doctrine scalaires (optionnel)
     * si false tous les champs du tableau seront passés aux setters de l’objet
     * @return T
     */
    public static function populateEntityFromArray(string|object $entityOrClass, array $data, bool $useDoctrineColumns = true): object
    {
        $entity = is_object($entityOrClass) ? $entityOrClass : new $entityOrClass();
        $columns = null;

        if ($useDoctrineColumns && is_string($entityOrClass)) {
            $className = is_string($entityOrClass) ? $entityOrClass : get_class($entity);
            $columns = self::getDoctrineColumns($className);
        }

        foreach ($data as $key => $value) {
            if ($columns !== null && !in_array($key, $columns, true)) {
                continue;
            }

            $setter = 'set' . ucfirst($key);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }

        return $entity;
    }
}
