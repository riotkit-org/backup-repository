<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\ClearExpiredTokensHandler;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ClearExpiredTokensController extends BaseController
{
    /**
     * @var ClearExpiredTokensController
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(ClearExpiredTokensHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @return Response
     */
    public function clearAction(): Response
    {
        return $this->wrap(
            function () {
                return new JsonResponse(
                    $this->handler->handle(
                        $this->authFactory->createFromToken($this->getLoggedUserToken())
                    )
                );
            }
        );
    }
}
