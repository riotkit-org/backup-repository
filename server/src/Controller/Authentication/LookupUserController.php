<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserAccountLookupHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LookupUserController extends BaseController
{
    private UserAccountLookupHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(UserAccountLookupHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Retrieve details of a specific user
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

        if ($response === null) {
            throw new NotFoundHttpException();
        }

        return new JsonFormattedResponse($response, JsonFormattedResponse::HTTP_OK);
    }
}
