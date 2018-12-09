<?php declare(strict_types=1);

namespace App\Infrastructure\Common\DependencyInjection;

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
        $commands = $container->findTaggedServiceIds('domain.bus');
        $commandDefinitions = array_map(
            function (string $serviceId) {
                return new Reference($serviceId);
            },
            array_keys($commands)
        );

        $container->setDefinition(
            DomainBus::class,
            new Definition(DomainBus::class, [$commandDefinitions])
        );
    }
}
