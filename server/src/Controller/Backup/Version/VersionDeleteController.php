<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\BackupVersionDeleteHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Version\VersionDeleteForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionDeleteController extends BaseController
{
    private BackupVersionDeleteHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(BackupVersionDeleteHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Delete a version
     *
     * @param Request $request
     * @param string  $collectionId
     * @param string  $backupId
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handleAction(Request $request, string $collectionId, string $backupId): Response
    {
        $requestData = [
            'collection' => $collectionId,
            'version'    => $backupId
        ];

        /**
         * @var VersionDeleteForm $form
         */
        $form = $this->decodeRequestIntoDTO($requestData, VersionDeleteForm::class);

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);

        $response = $this->handler->handle(
            $form,
            $this->authFactory->createVersioningContext($user, $form->collection),
            strtolower((string) $request->get('simulate')) !== 'true'
        );

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }
}
