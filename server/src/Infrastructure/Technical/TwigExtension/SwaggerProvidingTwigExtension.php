<?php declare(strict_types=1);

namespace App\Infrastructure\Technical\TwigExtension;

use App\Infrastructure\Technical\Service\SwaggerDocsProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SwaggerProvidingTwigExtension extends AbstractExtension
{
    private SwaggerDocsProvider $provider;

    public function __construct(SwaggerDocsProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_swagger_data', [$this, 'getSwaggerDataFunction'])
        ];
    }

    public function getSwaggerDataFunction(): array
    {
        return ['spec' => $this->provider->provide()];
    }
}
