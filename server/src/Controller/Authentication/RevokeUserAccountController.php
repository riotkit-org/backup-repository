<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserAccountDeleteHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

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
     * @SWG\Parameter(
     *     type="string",
     *     in="path",
     *     name="userId",
     *     description="Id of an user that should be deleted"
     * )
     *
     * @SWG\Response(
     *     response="201",
     *     description="Token was deleted",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="status",
     *             type="boolean",
     *             example="true"
     *         ),
     *         @SWG\Property(
     *             property="http_code",
     *             type="integer",
     *             example="201"
     *         ),
     *         @SWG\Property(
     *             property="errors",
     *             type="array",
     *             @SWG\Items(
     *                 type="string"
     *             )
     *         ),
     *         @SWG\Property(
     *             property="message",
     *             type="string",
     *             example="User was deleted"
     *         ),
     *         @SWG\Property(
     *             property="token",
     *             ref=@Model(type=\App\Domain\Authentication\Entity\Docs\Token::class)
     *         ),
     *          @SWG\Property(
     *             property="context",
     *             type="array",
     *             @SWG\Items(type="string")
     *         )
     *     )
     * )
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
