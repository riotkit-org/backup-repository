<?php declare(strict_types=1);

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

if (!\function_exists('getEnvValue')) {
    function getEnvValue(string $envName, $defaults)
    {
        $value = \getenv($envName);

        if ($value === null || $value === false) {
            return $defaults;
        }

        $toCompare = \is_string($value) ? \strtolower($value) : $value;

        if ($toCompare === 'false' || $toCompare === '0') {
            return false;
        }

        if ($toCompare === 'true' || $toCompare === '1') {
            return true;
        }

        return $value;
    }
}

/*
 * NOTICE: Application REBUILD (cache:clear) is MANDATORY for the changes to be applied in PROD mode
 */

$routes = new RouteCollection();

$hotlinkProtectionEnabled = getEnvValue('ANTI_HOTLINK_PROTECTION_ENABLED', true);
$restrictRegularUrls      = getEnvValue('ANTI_HOTLINK_RESTRICT_REGULAR_URLS', false);
$antiHotlinkRoute         = getEnvValue('ANTI_HOTLINK_URL', '/stream/{accessToken}/{expirationTime}/{fileId}');

$securedFromRawAccess = $hotlinkProtectionEnabled && $restrictRegularUrls;

//
// Regular download urls
//   - Can be optionally token-protected if hotlink protection is turned on
//
$routes->add('storage.get_file',
    new Route(
        '/public/download/{filename}',
        [
            '_controller' => [App\Controller\Storage\ViewFileController::class, 'handle'],
            '_secured' => $securedFromRawAccess
        ],
        [],
        [],
        '',
        [],
        ['GET']
    )
);

$routes->add('storage.get_file',
    new Route(
        '/repository/file/{filename}',
        [
            '_controller' => [App\Controller\Storage\ViewFileController::class, 'handle'],
            '_secured' => $securedFromRawAccess
        ],
        [],
        [],
        '',
        [],
        ['GET']
    )
);

//
// Hotlink protected URL
//
$routes->add('storage.antihotlink_get_file',
    new Route(
        $antiHotlinkRoute,
        [
            '_controller' => [App\Controller\Storage\AntiHotlinkViewFileController::class, 'handleAntiHotlinkUrl'],
            '_secured' => false
        ],
        [],
        [],
        '',
        [],
        ['GET']
    )
);

return $routes;
