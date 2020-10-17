<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\BackupSubmitHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\BackupSubmitForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubmitVersionController extends BaseController
{
    private BackupSubmitHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(BackupSubmitHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Send a new version to the collection
     *
     * @param Request $request
     * @param string $collectionId
     *
     * @return Response
     *
     * @throws Exception|\Throwable
     */
    public function handleAction(Request $request, string $collectionId): Response
    {
        return $this->withLongExecutionTimeAllowed(
            function () use ($request, $collectionId) {
                return $this->handleInternally($request, $collectionId);
            }
        );
    }

    /**
     * @param Request $request
     * @param string $collectionId
     *
     * @return Response
     *
     * @throws Exception|\Throwable
     */
    private function handleInternally(Request $request, string $collectionId): Response
    {
        /**
         * @var BackupSubmitForm $form
         */
        $form = $this->decodeRequestIntoDTO(['collection' => $collectionId], BackupSubmitForm::class);

        $response = $this->handler->handle(
            $form,
            $this->authFactory->createVersioningContext($this->getLoggedUser()),
            $this->getLoggedUser()
        );

        return new JsonFormattedResponse($response, $response->getExitCode());
    }
}
