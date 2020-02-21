<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenSearchHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenSearchController extends BaseController
{
    private TokenSearchHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(TokenSearchHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    public function searchAction(Request $request): Response
    {
        return $this->wrap(
            function () use ($request) {
                $securityContext = $this->authFactory->createFromToken($this->getLoggedUserToken());

                $response = $this->handler->handle(
                    (string) $request->get('q', ''),
                    (int) $request->get('page', 1),
                    (int) $request->get('limit', 50),
                    $securityContext
                );

                return new JsonResponse($response, JsonResponse::HTTP_OK);
            }
        );
    }
}
