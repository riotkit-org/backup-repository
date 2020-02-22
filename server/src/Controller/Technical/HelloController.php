<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Controller\BaseController;
use App\Domain\Common\Service\Versioning;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Swagger\Annotations as SWG;

/**
 * Lists all public routes
 */
class HelloController extends BaseController
{
    private Versioning $versioning;

    public function __construct(Versioning $versioning)
    {
        $this->versioning = $versioning;
    }

    public function sayHelloAction(): JsonResponse
    {
        return new JsonResponse(
            'Hello, welcome. Please take a look at /repository/routing/map for the list of available routes.',
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @SWG\Response(
     *     response="400",
     *     description="When not authorized with any token"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="String response code with version, in JSON format",
     *     @SWG\Schema(
     *          type="string",
     *          example="v3.0.0"
     *     )
     * )
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function showVersionAction(): JsonResponse
    {
        if ($this->getLoggedUserToken()->isAnonymous()) {
            throw new AccessDeniedHttpException();
        }

        return new JsonFormattedResponse($this->versioning->getVersion());
    }
}
