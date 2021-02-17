<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Controller\BaseController;
use App\Domain\Common\Service\Versioning;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Lists all public routes
 */
class HelloController extends BaseController
{
    private Versioning $versioning;
    private Connection $dbal;

    public function __construct(Versioning $versioning, Connection $dbal)
    {
        $this->versioning = $versioning;
        $this->dbal       = $dbal;
    }

    public function sayHelloAction(): JsonResponse
    {
        return new JsonResponse(
            'todo: Run Vue.js / React / any other frontend there',
            JsonResponse::HTTP_OK
        );
    }

    /**
     * Front page
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function showVersionAction(): JsonResponse
    {
        if ($this->getLoggedUser()->isAnonymous()) {
            throw new AccessDeniedHttpException();
        }

        return new JsonFormattedResponse([
            'version' => $this->versioning->getVersion(),
            'dbType'  => $this->dbal->getDriver()->getDatabasePlatform()->getName()
        ]);
    }
}
