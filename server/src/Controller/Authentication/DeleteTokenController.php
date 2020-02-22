<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenDeleteHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class DeleteTokenController extends BaseController
{
    private TokenDeleteHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(TokenDeleteHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param string $token
     *
     * @return JsonFormattedResponse
     *
     * @throws Exception
     */
    public function handle(string $token): Response
    {
        return $this->wrap(
            function () use ($token) {
                $response = $this->handler->handle(
                    $token,
                    $this->authFactory->createFromToken($this->getLoggedUserToken())
                );

                if ($response === null) {
                    return $this->createNotFoundResponse();
                }

                return new JsonFormattedResponse(
                    $response,
                    JsonFormattedResponse::HTTP_OK
                );
            }
        );
    }
}
