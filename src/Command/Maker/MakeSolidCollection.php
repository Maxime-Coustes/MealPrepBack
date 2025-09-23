<?php

namespace App\Command\Maker;

use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeSolidCollection extends AbstractMaker
{
    public function configureDependencies(DependencyBuilder  $dependencies): void
    {
        // Pas de dépendances supplémentaires pour ce Maker
    }

    public static function getCommandName(): string
    {
        return 'make:solid-collection';
    }

    public static function getCommandDescription(): string
    {
        return 'Crée une Collection pour une entité donnée (ex: TagCollection).';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(
            'entityName',
            InputArgument::REQUIRED,
            'Nom de l’entité (ex: Tag pour créer TagCollection)'
        );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityName = $input->getArgument('entityName');
        $collectionName = $entityName . 'Collection';
        $generator->generateClass(
            "App\\Entity\\$collectionName",
            __DIR__ . '/../../Resources/skeleton/solid_collection.tpl.php',
            [
                'entityClass' => "App\\Entity\\$entityName",
                'entityName' => $entityName,
                'collectionName' => $collectionName,
            ]
        );

        $generator->writeChanges();
        $io->success("Collection $collectionName générée ✅");
    }
}
