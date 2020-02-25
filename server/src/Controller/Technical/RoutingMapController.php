<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Lists all public routes
 */
class RoutingMapController extends AbstractController
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function listAction(): Response
    {
        $allRoutes = $this->router->getRouteCollection();

        return new JsonFormattedResponse(
            [
                'code' => JsonFormattedResponse::HTTP_OK,
                'data' => $this->serialize($this->findRepositoryRoutes($allRoutes))
            ],
            JsonFormattedResponse::HTTP_OK
        );
    }

    private function serialize(array $routes): array
    {
        return array_map(
            function (Route $route) {
                return [
                    'methods' => $route->getMethods(),
                    'path'    => $route->getPath()
                ];
            },
            $routes
        );
    }

    /**
     * @param RouteCollection|null $collection
     * @return Route[]
     */
    private function findRepositoryRoutes(RouteCollection $collection = null): array
    {
        if (!$collection) {
            return [];
        }

        return array_filter($collection->all(), function (Route $route) {
            return strpos($route->getPath(), '/_') !== 0;
        });
    }
}
