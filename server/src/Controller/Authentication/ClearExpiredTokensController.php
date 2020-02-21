<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\ClearExpiredTokensHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class ClearExpiredTokensController extends BaseController
{
    private ClearExpiredTokensHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(ClearExpiredTokensHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @SWG\Response(
     *     response="200",
     *     description="Status message and a log",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="message", type="string", example="Task done, log available."),
     *          @SWG\Property(
     *              property="log",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(property="id", type="string"),
     *                  @SWG\Property(property="date", type="string")
     *              )
     *          )
     *     )
     * )
     *
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
