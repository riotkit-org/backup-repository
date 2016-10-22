<?php

namespace Controllers\ServerInfo;

use Controllers\AbstractBaseController;
use Silex\ControllerCollection;
use Silex\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @package Controllers\Serverinfo
 */
class RoutingMapController extends AbstractBaseController
{
    /**
     * @return JsonResponse
     */
    public function viewAction()
    {
        /** @var Route[] $allRoutes */
        $allRoutes = $this->getContainer()->offsetGet('routes')->all();
        $matched   = [];

        foreach ($allRoutes as $route) {
            if (substr($route->getPath(), 0, 12) === '/repository/') {
                $matched[] = [
                    'methods' => $route->getMethods(),
                    'path'    => $route->getPath(),
                ];
            }
        }

        return new JsonResponse([
            'code'    => 200,
            'data'    => $matched,
        ], 200);
    }
}