<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\ClearExpiredTokensHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClearExpiredTokensController extends BaseController
{
    /**
     * @var ClearExpiredTokensController
     */
    private $handler;

    public function __construct(ClearExpiredTokensHandler $handler)
    {
        $this->handler = $handler;
    }

    public function clearAction(): JsonResponse
    {
        return new JsonResponse(
            $this->handler->handle()
        );
    }
}
