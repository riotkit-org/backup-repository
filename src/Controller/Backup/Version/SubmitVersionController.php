<?php declare(strict_types=1);

namespace App\Controller\Backup\Version;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Version\BackupSubmitHandler;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\BackupSubmitForm;
use App\Infrastructure\Backup\Form\BackupSubmitFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attach/detach a token of given Id to the collection
 */
class SubmitVersionController extends BaseController
{
    /**
     * @var BackupSubmitHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(BackupSubmitHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
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
        $form = new BackupSubmitForm();
        $infrastructureForm = $this->createForm(BackupSubmitFormType::class, $form);
        $infrastructureForm->submit([
            'collection' => $collectionId,
        ]);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request) {
                $response = $this->handler->handle(
                    $form,
                    $this->authFactory->createCollectionManagementContext($this->getLoggedUserToken()),
                    $this->createBaseUrl($request),
                    $this->getLoggedUserToken()
                );

                return new JsonResponse($response, $response->getExitCode());
            }
        );
    }
}
