<?php declare(strict_types=1);

namespace App\Infrastructure\Common\DependencyInjection;

use App\Infrastructure\Common\Service\ConfigParser;
use Aws\S3\S3Client;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Load all commands automatically into the DomainBus
 */
class FlysystemPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $adapters = (new ConfigParser(\App\FilesystemConfigDefinition::get()))->buildAdapters();

        foreach ($adapters as $adapter) {
            /**
             * AWS Simple Storage Service (S3)
             */
            if ($adapter['adapter'] === 'aws') {
                $container->setDefinition($adapter['options']['client'], new Definition(S3Client::class, [
                    '$args' => array_merge(
                        // defaults
                        [
                            'use_path_style_endpoint' => true,
                            'override_visibility_on_copy' => true
                        ],
                        $adapter['adapter_args']
                    )
                ]));

            /**
             * Google Cloud Storage (GCS)
             */
            } elseif ($adapter['adapter'] === 'gcloud') {
                $container->setDefinition($adapter['options']['client'], new Definition(StorageClient::class, [
                    '$config' => $adapter['adapter_args']
                ]));
            }
        }
    }
}
