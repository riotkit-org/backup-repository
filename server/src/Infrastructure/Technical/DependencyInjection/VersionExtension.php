<?php declare(strict_types=1);

namespace App\Infrastructure\Technical\DependencyInjection;

use App\Domain\Common\Service\Versioning;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class VersionExtension
{
    /**
     * @inheritDoc
     */
    public static function load(ContainerBuilder $container)
    {
        $service = new Versioning();
        $container->setParameter('APP_VERSION', $service->getVersion());
    }
}
