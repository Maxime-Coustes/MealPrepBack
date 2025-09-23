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

class MakeSolidService extends AbstractMaker
{
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // Pas de dépendances supplémentaires pour ce Maker
    }

    public static function getCommandName(): string
    {
        return 'make:solid-service';
    }

    public static function getCommandDescription(): string
    {
        return 'Crée un service avec repository et interface basés sur le nom de l’entité';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command->addArgument(
            'entityName',
            InputArgument::REQUIRED,
            'Nom de l’entité (ex: Recipe pour App\Entity\Recipe)'
        );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityName = $input->getArgument('entityName');

        // Déduire noms et namespaces
        $entityClass = "App\\Entity\\$entityName";
        $interfaceName = "{$entityName}ServiceInterface";
        $interfaceNamespace = "App\\Interface\\$interfaceName";
        $repositoryClass = "App\\Repository\\{$entityName}Repository";
        $repositoryShortName = "{$entityName}Repository";
        $serviceName = "{$entityName}Service";

        $generator->generateClass(
            "App\\Service\\$serviceName",
            __DIR__ . '/../../Resources/skeleton/solid_service.tpl.php',
            [
                'name' => $serviceName,
                'interface' => $interfaceName,
                'interfaceNamespace' => $interfaceNamespace,
                'entityClass' => $entityClass,
                'repositoryClass' => $repositoryClass,
                'repositoryShortName' => $repositoryShortName,
            ]
        );

        $generator->writeChanges();

        $io->success("Service $serviceName généré avec interface $interfaceName, repository $repositoryClass et entité $entityClass ✅");
    }
}
