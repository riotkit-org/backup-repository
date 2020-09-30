<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\VersionsListingHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Version\VersionsListingForm;
use App\Infrastructure\Backup\Form\Version\VersionListingFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attach/detach a token of given Id to the collection
 */
class VersionListingController extends BaseController
{
    private VersionsListingHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(VersionsListingHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * List versions in a collection
     *
     * @param string $collectionId
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handleAction(string $collectionId): Response
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
            function () use ($form) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createVersioningContext($this->getLoggedUser())
                );

                return new JsonFormattedResponse($response, $response->getExitCode());
            }
        );
    }
}
