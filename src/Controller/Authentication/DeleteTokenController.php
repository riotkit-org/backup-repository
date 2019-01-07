<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenDeleteHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteTokenController extends BaseController
{
    /**
     * @var TokenDeleteHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(TokenDeleteHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param string $token
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function handle(string $token): Response
    {
        return $this->wrap(
            function () use ($token) {
                return new JsonResponse(
                    $this->handler->handle(
                        $token,
                        $this->authFactory->createFromToken($this->getLoggedUserToken())
                    ),
                    JsonResponse::HTTP_OK
                );
            }
        );
    }
}
