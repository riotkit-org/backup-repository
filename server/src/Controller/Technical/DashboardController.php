<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Controller\BaseController;
use App\Domain\Authentication\Entity\User;
use App\Domain\Technical\ActionHandler\DashboardHandler;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends BaseController
{
    private DashboardHandler $handler;

    public function __construct(DashboardHandler $handler)
    {
        $this->handler = $handler;
    }

    public function showMetricsAction(): Response
    {
        return new JsonFormattedResponse(
            $this->handler->handle($this->getLoggedUser(User::class))
        );
    }
}
