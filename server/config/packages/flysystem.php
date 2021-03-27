<?php declare(strict_types=1);

use App\Infrastructure\Common\Service\ConfigParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

require_once __DIR__ . '/../../src/Infrastructure/Common/Service/ConfigParser.php';

$configParser = new ConfigParser(App\FilesystemConfigDefinition::get());
$adapters = $configParser->buildAdapters();

/**
 * @var ContainerInterface $container
 */
unset($adapters['default_adapter']['adapter_args']);
unset($adapters['ro_adapter']['adapter_args']);

$container->loadFromExtension('flysystem', [
    'storages' => [
        'readwrite_filesystem.storage' => $adapters['default_adapter'],
        'readonly_filesystem.storage'  => $adapters['ro_adapter'] ?? $adapters['default_adapter']
    ]
]);
