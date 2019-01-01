<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\VersionsListingHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Version\VersionsListingForm;
use App\Infrastructure\Backup\Form\Version\VersionListingFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attach/detach a token of given Id to the collection
 */
class VersionListingController extends BaseController
{
    /**
     * @var VersionsListingHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(VersionsListingHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     * @param string $collectionId
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $collectionId): Response
    {
        $form = new VersionsListingForm();
        $infrastructureForm = $this->createForm(VersionListingFormType::class, $form);
        $infrastructureForm->submit([
            'collection' => $collectionId
        ]);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createVersioningContext($this->getLoggedUserToken()),
                    $this->createBaseUrl($request)
                );

                return new JsonResponse($response, $response->getExitCode());
            }
        );
    }
}
