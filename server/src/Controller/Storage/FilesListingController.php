<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\FilesListingHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\FilesListingForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleListing(Request $request): Response
    {
        /**
         * @var FilesListingForm $form
         */
        $form = $this->decodeRequestIntoDTO($request->query->all(), FilesListingForm::class);

        $securityContext = $this->authFactory
            ->createListingContextFromTokenAndForm($this->getLoggedUser(), $form);

        return new JsonFormattedResponse(
            $this->handler->handle(
                $form,
                $securityContext
            ),
            JsonFormattedResponse::HTTP_OK
        );
    }
}
