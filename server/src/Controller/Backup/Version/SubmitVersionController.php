<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\BackupSubmitHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\BackupSubmitForm;
use App\Domain\Backup\ValueObject\JWT;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubmitVersionController extends BaseController
{
    public function __construct(private BackupSubmitHandler $handler,
                                private SecurityContextFactory $authFactory,
                                private LoggerInterface $logger) { }

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

        /**
         * @var User $user
         * @var JWT $accessToken
         */
        $user = $this->getLoggedUser(User::class);
        $accessToken = $this->getCurrentSessionToken($request, JWT::class);

        $response = $this->handler->handle(
            $form,
            $this->authFactory->createVersioningContext($user, $form->collection),
            $user,
            $accessToken
        );

        $this->logger->debug('Upload finished, creating JsonFormattedResponse');
        $this->logger->debug('Client has aborted connection? = ' . (int) connection_aborted());

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
