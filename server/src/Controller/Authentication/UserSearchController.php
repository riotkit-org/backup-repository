<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserSearchHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSearchController extends BaseController
{
    private UserSearchHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(UserSearchHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Search for users accounts
     *
     * @param Request $request
     * @return JsonFormattedResponse
     * @throws Exception
     */
    public function searchAction(Request $request): Response
    {
        $securityContext = $this->authFactory->createFromUserAccount($this->getLoggedUser());

        $response = $this->handler->handle(
            (string) $request->get('q', ''),
            (int) $request->get('page', 1),
            (int) $request->get('limit', 50),
            $securityContext
        );

        return new JsonFormattedResponse($response, JsonFormattedResponse::HTTP_OK);
    }
}
