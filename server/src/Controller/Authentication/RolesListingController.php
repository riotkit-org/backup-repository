<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\RolesListingHandler;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RolesListingController extends BaseController
{
    private RolesListingHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(RolesListingHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * List available roles
     *
     * @param Request $request
     *
     * @return JsonFormattedResponse
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request): Response
    {
        $securityContext = $this->authFactory->createFromUserAccount($this->getLoggedUser());
        $limits = explode(',', str_replace(' ', '', $request->query->get('limits', '')));

        return new JsonFormattedResponse($this->handler->handle($securityContext, $this->getLoggedUser(), $limits));
    }
}
