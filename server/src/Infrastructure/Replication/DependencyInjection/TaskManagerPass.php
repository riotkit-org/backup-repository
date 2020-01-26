<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\DependencyInjection;

use App\Domain\Replication\Contract\TaskProcessor;
use App\Domain\Replication\ActionHandler\Client\ProcessingWorkerActionHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Load all commands automatically into the DomainBus
 */
class TaskManagerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $commands = $container->getDefinitions();
        $onlyCommandsThatImplementsInterface = array_filter(
            $commands,
            static function (Definition $definition) {

                if (!$definition->getClass() || !class_exists($definition->getClass(), false)) {
                    return false;
                }

                return \in_array(TaskProcessor::class, class_implements($definition->getClass()), true);
            }
        );

        $commandDefinitions = array_map(
            static function (string $serviceId) {
                return new Reference($serviceId);
            },
            array_keys($onlyCommandsThatImplementsInterface)
        );

        $definition = new Definition(ProcessingWorkerActionHandler::class);
        $definition->addArgument($commandDefinitions);
        $container->setDefinition(ProcessingWorkerActionHandler::class, $definition);
    }
}
