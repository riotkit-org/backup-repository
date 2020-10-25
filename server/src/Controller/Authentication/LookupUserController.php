<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserAccountLookupHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
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
     * @SWG\Parameter(
     *     type="string",
     *     in="path",
     *     name="userId",
     *     description="Id of an user to lookup"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Shows details about given user",
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
     *             example="200"
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
     *             example="User found"
     *         ),
     *         @SWG\Property(
     *             property="user",
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

        if ($response === null) {
            throw new NotFoundHttpException();
        }

        return new JsonFormattedResponse($response, JsonFormattedResponse::HTTP_OK);
    }
}
