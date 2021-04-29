<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Controller\BaseController;
use App\Domain\Common\Service\Versioning;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class HelloController extends BaseController
{
    private Versioning $versioning;
    private Connection $dbal;

    public function __construct(Versioning $versioning, Connection $dbal)
    {
        $this->versioning = $versioning;
        $this->dbal       = $dbal;
    }

    /**
     * @return Response
     *
     * @throws \Exception
     */
    public function sayHelloAction(): Response
    {
        $frontendPath = __DIR__ . '/../../../public/frontend.html';

        if (!is_file($frontendPath)) {
            throw new \Exception('Please build frontend first, then move files to public/ directory, rename index.html to frontend.html');
        }

        return new Response(file_get_contents($frontendPath), Response::HTTP_OK);
    }

    public function showVersionAction(): JsonResponse
    {
        if ($this->getLoggedUser()->isAnonymous()) {
            throw new AccessDeniedHttpException();
        }

        return new JsonFormattedResponse([
            'version' => $this->versioning->getVersion(),
            'db_type' => $this->dbal->getDriver()->getDatabasePlatform()->getName()
        ]);
    }
}
