<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Domain\Technical\ActionHandler\HealthCheckHandler;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;

/**
 * Returns status information
 */
class HealthController extends AbstractController
{
    private HealthCheckHandler $handler;

    public function __construct(HealthCheckHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Show application status
     *
     * @SWG\Response(
     *     response="400",
     *     description="When the 'code' parameter does not match the 'HEALTH_CHECK_CODE' setting"
     * )
     *
     * @SWG\Response(
     *     response="503",
     *     description="Same response format as for 200 code. 503 is when at least one check from list failed"
     * )
     *
     * @SWG\Parameter(
     *     type="string",
     *     name="code",
     *     description="Secret code given to monitoring tool, so nobody else can access this specific endpoint",
     *     in="query"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Health report",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="status",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(property="storage", type="boolean"),
     *                  @SWG\Property(property="database", type="boolean")
     *              )
     *          ),
     *          @SWG\Property(
     *              property="messages",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(property="storage", type="array",
     *                      @SWG\Items(type="string")
     *                  ),
     *                  @SWG\Property(property="database", type="array",
     *                      @SWG\Items(type="string")
     *                  )
     *              )
     *          ),
     *          @SWG\Property(property="global_status", type="boolean"),
     *          @SWG\Property(
     *              property="ident",
     *              type="array",
     *              @SWG\Items(
     *                  type="string"
     *              )
     *          )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function healthAction(Request $request): JsonResponse
    {
        $result = $this->handler->handle($request->get('code', ''));

        return new JsonFormattedResponse(
            $result['response'],
            $result['status'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_SERVICE_UNAVAILABLE,
            []
        );
    }
}
