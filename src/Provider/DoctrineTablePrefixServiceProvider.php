<?php declare(strict_types=1);

namespace Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\ORM\Events;
use Doctrine\Extension\TablePrefix;

/**
 * Original code from https://github.com/valeriangalliat/doctrine-table-prefix-service-provider
 */
class DoctrineTablePrefixServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['dbs.event_manager'] = $app->extend('dbs.event_manager', function ($managers, $app) {
            $app['dbs.options.initializer']();

            foreach ($app['dbs.options'] as $name => $options) {
                if (isset($options['prefix'])) {
                    $tablePrefix = new TablePrefix($options['prefix']);

                    /* @var $managers \Doctrine\Common\EventManager[] */
                    $managers[$name]->addEventListener(
                        Events::loadClassMetadata,
                        $tablePrefix
                    );
                }
            }

            return $managers;
        });
    }
}
