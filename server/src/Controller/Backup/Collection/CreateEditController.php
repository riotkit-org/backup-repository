<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\CreationHandler;
use App\Domain\Backup\ActionHandler\Collection\EditHandler;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Form\Collection\EditForm;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Infrastructure\Backup\Form\Collection\CreationFormType;
use App\Infrastructure\Backup\Form\Collection\EditFormType;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateEditController extends BaseController
{
    private CreationHandler $createHandler;
    private EditHandler $editHandler;
    private SecurityContextFactory $authFactory;

    public function __construct(
        CreationHandler $handler,
        EditHandler $editHandler,
        SecurityContextFactory $authFactory
    ) {
        $this->createHandler = $handler;
        $this->editHandler   = $editHandler;
        $this->authFactory   = $authFactory;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function handleAction(Request $request): Response
    {
        $isCreation = strtoupper($request->getMethod()) === 'POST';

        $form = $isCreation ? new CreationForm() : new EditForm();
        $infrastructureForm = $this->submitFormFromJsonRequest(
            $request,
            $form,
            $isCreation ? CreationFormType::class : EditFormType::class
        );

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form, $request, $isCreation) {
                $response = $this->handle(
                    $form,
                    $this->authFactory->createCollectionManagementContext($this->getLoggedUserToken()),
                    $isCreation
                );

                if ($request->query->get('simulate') !== 'true') {
                    $this->createHandler->flush();
                }

                return new JsonFormattedResponse($response, $response->getHttpCode());
            }
        );
    }

    /**
     * @param $form
     * @param $auth
     * @param bool $isCreation
     *
     * @return CrudResponse
     *
     * @throws AuthenticationException
     */
    private function handle($form, $auth, bool $isCreation): CrudResponse
    {
        if ($isCreation) {
            return $this->createHandler->handle($form, $auth);
        }

        return $this->editHandler->handle($form, $auth);
    }
}
