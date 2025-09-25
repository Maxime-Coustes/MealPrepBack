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
}
