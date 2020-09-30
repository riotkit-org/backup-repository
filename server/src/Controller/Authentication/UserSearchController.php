<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserSearchHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

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
     * @SWG\Parameter(
     *     type="string",
     *     in="query",
     *     name="q",
     *     description="Query string, a search phrase. Notice: If 'security.cannot_see_full_token_ids' restriction is applied on current viewer token, then search phrase will only consider non-asterisk characters for safety"
     * )
     *
     * @SWG\Parameter(
     *     type="integer",
     *     in="query",
     *     name="limit",
     *     description="Number of entries returned by the request"
     * )
     *
     * @SWG\Parameter(
     *     type="integer",
     *     in="query",
     *     name="page",
     *     description="Currenty fetched page"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Search users by id and associted fields",
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
     *             example="Matches found"
     *         ),
     *         @SWG\Property(
     *             property="context",
     *             type="object",
     *             @SWG\Property(
     *                 property="pagination",
     *                 ref=@Model(type=\App\Domain\Common\Entity\Docs\Pagination::class)
     *             )
     *         ),
     *         @SWG\Property(
     *             property="data",
     *             type="array",
     *             @SWG\Items(
     *                 ref=@Model(type=\App\Domain\Authentication\Entity\Docs\Token::class)
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonFormattedResponse
     * @throws Exception
     */
    public function searchAction(Request $request): Response
    {
        return $this->wrap(
            function () use ($request) {
                $securityContext = $this->authFactory->createFromUserAccount($this->getLoggedUser());

                $response = $this->handler->handle(
                    (string) $request->get('q', ''),
                    (int) $request->get('page', 1),
                    (int) $request->get('limit', 50),
                    $securityContext
                );

                return new JsonFormattedResponse($response, JsonFormattedResponse::HTTP_OK);
            }
        );
    }
}
