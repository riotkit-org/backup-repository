<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserAccountDeleteHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class RevokeUserAccountController extends BaseController
{
    private UserAccountDeleteHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(UserAccountDeleteHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Revoke an access for given user
     *
     * @param string $userId
     *
     * @return JsonFormattedResponse
     *
     * @throws Exception
     */
    public function handle(string $userId): Response
    {
        $response = $this->handler->handle(
            $userId,
            $this->authFactory->createFromUserAccount($this->getLoggedUser())
        );

        return new JsonFormattedResponse(
            $response,
            $response->getHttpCode()
        );
    }
}
