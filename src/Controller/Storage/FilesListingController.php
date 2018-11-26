<?php declare(strict_types=1);

namespace App\Controller\Storage;

use App\Controller\BaseController;
use App\Domain\Storage\ActionHandler\FilesListingHandler;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Form\FilesListingForm;
use App\Infrastructure\Storage\Form\FilesListingFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilesListingController extends BaseController
{
    /**
     * @var FilesListingHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(FilesListingHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
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
            ->createListingContextFromTokenAndForm($this->getLoggedUserToken(), $form);

        return $this->wrap(
            function () use ($form, $securityContext, $request) {
                return new JsonResponse(
                    $this->handler->handle(
                        $form,
                        $securityContext,
                        $this->createBaseUrl($request)
                    ),
                    JsonResponse::HTTP_OK
                );
            }
        );
    }
}
