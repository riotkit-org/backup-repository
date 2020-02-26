<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\ListingHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\ListingForm;
use App\Infrastructure\Backup\Form\Collection\ListingFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class ListingController extends BaseController
{

    private ListingHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        ListingHandler         $handler,
        SecurityContextFactory $authFactory
    ) {
        $this->handler       = $handler;
        $this->authFactory   = $authFactory;
    }

    /**
     * @SWG\Parameter(
     *     type="string",
     *     in="query",
     *     name="searchQuery",
     *     description="Search phrase"
     * )
     *
     * @SWG\Parameter(
     *     type="array",
     *     in="query",
     *     name="tags",
     *     description="List of tags to search by",
     *     @SWG\Items(type="string")
     * )
     *
     * @SWG\Parameter(
     *     type="array",
     *     in="query",
     *     name="allowedTokens",
     *     description="Filter by allowed tokens",
     *     @SWG\Items(type="string")
     * )
     *
     * @SWG\Parameter(
     *     type="string",
     *     in="query",
     *     name="createdFrom",
     *     description="Creation date"
     * )
     *
     * @SWG\Parameter(
     *     type="string",
     *     in="query",
     *     name="createdTo",
     *     description="Created before this date"
     * )
     *
     *  @SWG\Parameter(
     *     type="integer",
     *     in="query",
     *     name="limit",
     *     description="Limit returned results"
     * )
     *
     * @SWG\Parameter(
     *     type="integer",
     *     in="query",
     *     name="page",
     *     description="Current page"
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Lists collections",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="status",
     *             type="boolean",
     *             example=true
     *         ),
     *         @SWG\Property(
     *             property="http_code",
     *             type="integer",
     *             example="200"
     *         ),
     *         @SWG\Property(
     *             property="elements",
     *             type="array",
     *             @SWG\Items(
     *                 ref=@Model(type=\App\Domain\Backup\Entity\Docs\CollectionDoc::class)
     *             )
     *         ),
     *         @SWG\Property(
     *             property="pagination",
     *             ref=@Model(type=\App\Domain\Common\Entity\Docs\PaginationDoc::class)
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handleAction(Request $request): Response
    {
        $form = new ListingForm();
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, ListingFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                $securityContext = $this->authFactory->createCollectionManagementContext(
                    $this->getLoggedUserToken()
                );

                $response = $this->handler->handle($form, $securityContext);

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }
}
