<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\VersionsListingHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Version\VersionsListingForm;
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
        /**
         * @var VersionsListingForm $form
         */
        $form = $this->decodeRequestIntoDTO(['collection' => $collectionId], VersionsListingForm::class);

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);

        $response = $this->handler->handle(
            $form,
            $this->authFactory->createVersioningContext($user, $form->collection)
        );

        return new JsonFormattedResponse($response, $response->getExitCode());
    }
}
