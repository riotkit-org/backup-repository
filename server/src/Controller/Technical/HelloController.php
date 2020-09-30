<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Controller\BaseController;
use App\Domain\Common\Service\Versioning;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Swagger\Annotations as SWG;

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
     * @SWG\Response(
     *     response="400",
     *     description="When not authorized with any token"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="String response code with version, in JSON format",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="version",
     *              type="string",
     *              example="3.0.0",
     *              description="Full application version, including release type (if any). Examples: 3.0.0 for stable release, 3.0.0-dev for development version, 3.0.0-RC1 for release candidate, 3.0.0-alpha for alpha release."
     *          ),
     *          @SWG\Property(
     *              property="dbType",
     *              type="string",
     *              example="postgresql",
     *              description="Doctrine ORM database platform name"
     *          )
     *     )
     * )
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
