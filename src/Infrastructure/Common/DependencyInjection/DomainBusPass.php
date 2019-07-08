<?php declare(strict_types=1);

namespace App\Infrastructure\Common\DependencyInjection;

use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Common\Service\Bus\DomainBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Load all commands automatically into the DomainBus
 */
class DomainBusPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $commands = $container->getDefinitions();
        $onlyCommandsThatImplementsInterface = array_filter(
            $commands,
            function (Definition $definition) {

                if (!$definition->getClass() || !class_exists($definition->getClass(), false)) {
                    return false;
                }

                return \in_array(CommandHandler::class, class_implements($definition->getClass()), true);
            }
        );

        $commandDefinitions = array_map(
            function (string $serviceId) {
                return new Reference($serviceId);
            },
            array_keys($onlyCommandsThatImplementsInterface)
        );

        $definition = new Definition(DomainBus::class);
        $definition->addMethodCall('setCommands', [$commandDefinitions]);

        $container->setDefinition(DomainBus::class, $definition);

    }
}
