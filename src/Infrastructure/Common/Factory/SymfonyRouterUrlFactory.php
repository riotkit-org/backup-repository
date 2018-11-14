<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Factory;

use App\Domain\Common\Factory\UrlFactory;
use Symfony\Component\Routing\RouterInterface;

class SymfonyRouterUrlFactory implements UrlFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function generate(string $routeName, array $vars): string
    {
        return $this->router->generate($routeName, $vars);
    }
}
