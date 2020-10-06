<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\FilesListingHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\FilesListingForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Storage\Form\FilesListingFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class FilesListingController extends BaseController
{
    private FilesListingHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(FilesListingHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Search for files in the storage
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     default=1
     * )
     *
     * @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     type="string",
     *     default=""
     * )
     *
     * @SWG\Parameter(
     *     name="searchQuery",
     *     in="query",
     *     type="string"
     * )
     *
     * @SWG\Parameter(
     *     name="tags",
     *     in="query",
     *     type="array",
     *     @SWG\Items(type="string")
     * )
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns a list of files in the storage, matching given search criteria",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="results",
     *             type="array",
     *             @SWG\Items(ref=@Model(type=\App\Domain\Storage\Entity\Docs\StoredFile::class))
     *         ),
     *         @SWG\Property(
     *             property="pagination",
     *             type="object",
     *             ref=@Model(type=\App\Domain\Common\Entity\Docs\Pagination::class)
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleListing(Request $request): Response
    {
        $form = new FilesListingForm();
        $infrastructureForm = $this->submitFormFromRequestQuery($request, $form, FilesListingFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        $securityContext = $this->authFactory
            ->createListingContextFromTokenAndForm($this->getLoggedUser(), $form);

        return $this->wrap(
            function () use ($form, $securityContext) {
                return new JsonFormattedResponse(
                    $this->handler->handle(
                        $form,
                        $securityContext
                    ),
                    JsonFormattedResponse::HTTP_OK
                );
            }
        );
    }
}
