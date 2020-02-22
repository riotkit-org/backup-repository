<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\RolesListingHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
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
     * @return JsonFormattedResponse
     *
     * @throws Exception
     */
    public function handle(): Response
    {
        return $this->wrap(function () {
            $securityContext = $this->authFactory->createFromToken($this->getLoggedUserToken());

            return new JsonFormattedResponse($this->handler->handle($securityContext));
        });
    }
}
