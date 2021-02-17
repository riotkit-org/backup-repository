<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Domain\Technical\ActionHandler\HealthCheckHandler;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
