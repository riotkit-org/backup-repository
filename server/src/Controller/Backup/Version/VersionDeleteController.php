<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\BackupVersionDeleteHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Version\VersionDeleteForm;
use App\Infrastructure\Backup\Form\Version\VersionDeleteFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionDeleteController extends BaseController
{
    /**
     * @var BackupVersionDeleteHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(BackupVersionDeleteHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler     = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     * @param string  $collectionId
     * @param string  $backupId
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request, string $collectionId, string $backupId): Response
    {
        $form = new VersionDeleteForm();
        $infrastructureForm = $this->createForm(VersionDeleteFormType::class, $form);
        $infrastructureForm->submit([
            'collection' => $collectionId,
            'version'    => $backupId
        ]);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createVersioningContext($this->getLoggedUserToken()),
                    strtolower((string) $request->get('simulate')) !== 'true'
                );

                return new JsonResponse($response, $response->getExitCode());
            }
        );
    }
}
