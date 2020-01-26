<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\DependencyInjection;

use App\Domain\Replication\Manager\Client\CompatibilityManager;
use App\Domain\Replication\Service\EndpointValidator\EndpointValidator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EndpointValidatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /**
         * @var Definition[] $services
         */
        $services = array_filter(
            $container->getDefinitions(),
            static function (Definition $definition) {

                if (!$definition->getClass() || !class_exists($definition->getClass(), false)) {
                    return false;
                }

                return \in_array(EndpointValidator::class, class_implements($definition->getClass()), true);
            }
        );

        $handlers = array_map(
            static function (Definition $serviceName) {
                return new Reference($serviceName->getClass());
            },
            $services
        );

        $def = $container->getDefinition(CompatibilityManager::class);
        $def->setArguments(['$validators' => $handlers]);

        $container->setDefinition(CompatibilityManager::class, $def);
    }
}
