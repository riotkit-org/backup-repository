<?php declare(strict_types=1);

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/*
 * NOTICE: Application REBUILD (cache:clear) is MANDATORY for the changes to be applied in PROD mode
 */

$routes = new RouteCollection();

$hotlinkProtectionEnabled = getenv('ANTI_HOTLINK_PROTECTION_ENABLED') ?? true;
$restrictRegularUrls       = getenv('ANTI_HOTLINK_RESTRICT_REGULAR_URLS') ?? false;
$antiHotlinkRoute         = getenv('ANTI_HOTLINK_URL') ?: '/stream/{accessToken}/{expirationTime}/{fileId}';

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
