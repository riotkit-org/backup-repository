<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenSearchHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class TokenSearchController extends BaseController
{
    private TokenSearchHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(TokenSearchHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Search for tokens
     *
     * @SWG\Parameter(
     *     type="string",
     *     in="query",
     *     name="q",
     *     description="Query string, a search phrase"
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
     * @SWG\Parameter(
     *     type="string",
     *     in="query",
     *     name="q",
     *     description="Query string, a search phrase"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Search tokens by id and associted fields",
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
                $securityContext = $this->authFactory->createFromToken($this->getLoggedUserToken());

                $response = $this->handler->handle(
                    (string) $request->get('q', ''),
                    (int) $request->get('page', 1),
                    (int) $request->get('limit', 50),
                    $securityContext
                );

                // @todo: Disable searching by id, when id censorship is turned on

                return new JsonFormattedResponse($response, JsonFormattedResponse::HTTP_OK);
            }
        );
    }
}
