<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\DependencyInjection;

use App\Domain\Storage\Service\AlternativeFilenameResolver;
use App\Domain\Storage\Service\HotlinkPatternResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

class AntiHotlinkFeatureCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        $container->setDefinition(
            AlternativeFilenameResolver::class,
            new Definition(AlternativeFilenameResolver::class, [
                '$mapping' => $this->getIdsMapping()
            ])
        );

        $container->setDefinition(
            HotlinkPatternResolver::class,
            new Definition(HotlinkPatternResolver::class, [
                '$pattern'   => getenv('ANTI_HOTLINK_SECRET_METHOD') ?? '',
                '$algorithm' => getenv('ANTI_HOTLINK_CRYPTO'),
                '$logger'    => new Reference(LoggerInterface::class)
            ])
        );
    }

    private function getIdsMapping(): array
    {
        $path = __DIR__ . '/../../../../config/ids_mapping.yaml';

        if (\is_file($path)) {
            return Yaml::parse(file_get_contents($path));
        }

        return [];
    }
}
