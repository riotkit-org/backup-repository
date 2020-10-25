<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Technical\Service\SwaggerDocsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SwaggerDocsController extends AbstractController
{
    private SwaggerDocsProvider $provider;

    public function __construct(SwaggerDocsProvider $provider)
    {
        $this->provider = $provider;
    }

    public function serveSwaggerFileAction(): Response
    {
        return new JsonFormattedResponse($this->provider->provide());
    }
}
