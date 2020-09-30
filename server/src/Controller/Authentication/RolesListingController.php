<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\RolesListingHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

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
     * @SWG\Response(
     *     response="200",
     *     description="Lists avaialble roles",
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
     *             type="array",
     *             @SWG\Items(
     *                 type="string"
     *             )
     *         ),
     *         @SWG\Property(
     *             property="data",
     *             type="array",
     *             @SWG\Items(
     *                 type="object",
     *                 @SWG\Property(
     *                     property="role_name",
     *                     type="string",
     *                     example="Some description"
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonFormattedResponse
     *
     * @throws Exception
     */
    public function handle(): Response
    {
        return $this->wrap(function () {
            $securityContext = $this->authFactory->createFromUserAccount($this->getLoggedUser());

            return new JsonFormattedResponse($this->handler->handle($securityContext));
        });
    }
}
