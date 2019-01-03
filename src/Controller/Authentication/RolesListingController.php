<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\RolesListingHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RolesListingController extends BaseController
{
    /**
     * @var RolesListingHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(RolesListingHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @return Response
     *
     * @throws \Exception
     */
    public function handle(): Response
    {
        return $this->wrap(function () {
            $securityContext = $this->authFactory->createFromToken($this->getLoggedUserToken());

            return new JsonResponse($this->handler->handle($securityContext));
        });
    }
}
