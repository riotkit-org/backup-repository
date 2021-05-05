<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Controller\BaseController;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Technical\ActionHandler\DashboardHandler;
use App\Domain\Technical\Service\InfluxDBMetricsFormatter;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DashboardController extends BaseController
{
    public function __construct(
        private DashboardHandler $handler,
        private InfluxDBMetricsFormatter $influxFormatter,
        private string $metricsCode,
        private string $baseUrl,
        private string $appEnv
    ) { }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws AuthenticationException
     * @throws CommonValueException
     */
    public function showMetricsAction(Request $request): Response
    {
        try {
            $user = $this->getLoggedUser(User::class);

        } catch (AuthenticationException | AccessDeniedHttpException $e) {
            if ($request->get('code') !== $this->metricsCode) {
                throw $e;
            }

            return $this->format(
                $request->get('format', ''),
                $this->handler->handle(userContext: null, isSystemContext: true, formatAsHumanReadable: false)
            );
        }

        return $this->format(
            $request->get('format', ''), $this->handler->handle($user)
        );
    }

    private function format(string $format, array $toFormat): Response
    {
        if ($format === 'influxdb') {
            return new Response(
                $this->influxFormatter->format(toFormat: $toFormat, baseUrl: $this->baseUrl, appEnv: $this->appEnv)
            );
        }

        // default format is JSON
        return new JsonFormattedResponse($toFormat);
    }
}
