<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\DependencyInjection;

use App\Domain\Replication\ActionHandler\BaseReplicationHandler;
use App\Domain\Replication\Provider\ConfigurationProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Injects ConfigurationProvider instance into all classes that extends BaseReplicationHandler
 */
class ConfigurationProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /**
         * @var Definition[] $handlers
         */
        $handlers = array_filter(
            $container->getDefinitions(),
            static function (Definition $definition) {

                if (!$definition->getClass() || !class_exists($definition->getClass(), false)) {
                    return false;
                }

                return \in_array(BaseReplicationHandler::class, class_parents($definition->getClass()), true);
            }
        );

        foreach ($handlers as $handler) {
            $handler->addMethodCall('setConfigurationProvider', [new Reference(ConfigurationProvider::class)]);
        }
    }
}
