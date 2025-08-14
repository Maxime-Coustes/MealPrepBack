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

class MakeSolidInterface extends AbstractMaker
{
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // Pas de dépendances supplémentaires pour ce Maker
    }

    public static function getCommandName(): string
    {
        return 'make:solid-interface';
    }

    public static function getCommandDescription(): string
    {
        return 'Crée une interface basée sur le nom de l’entité';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(
            'entityName',
            InputArgument::REQUIRED,
            'Nom de l’entité (ex: Recipe pour App\Entity\Recipe)'
        );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityName = $input->getArgument('entityName');

        // Déduire nom et namespace de l’interface
        $interfaceName = "{$entityName}ServiceInterface";
        $interfaceNamespace = "App\\Interface\\$interfaceName";

        $generator->generateClass(
            $interfaceNamespace,
            __DIR__ . '/../../Resources/skeleton/solid_interface.tpl.php',
            [
                'interfaceName' => $interfaceName,
                'entityName' => $entityName,
            ]
        );

        $generator->writeChanges();

        $io->success("Interface $interfaceName générée avec succès ✅");
    }
}
