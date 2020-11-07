<?php declare(strict_types=1);

namespace App\Controller\Backup\Collection;

use App\Controller\BaseController;
use App\Domain\Backup\ActionHandler\Collection\CreationHandler;
use App\Domain\Backup\ActionHandler\Collection\EditHandler;
use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Factory\SecurityContextFactory;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Form\Collection\EditForm;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * Create (POST), edit (PUT) a versioned file-collection that will keep historic versions of file, and rotate them.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function handleAction(Request $request): Response
    {
        $isCreation = strtoupper($request->getMethod()) === 'POST';

        /**
         * @var CreationForm|EditForm $form
         */
        $form = $this->decodeRequestIntoDTO($request, $isCreation ? CreationForm::class : EditForm::class);

        /**
         * @var User $user
         */
        $user = $this->getLoggedUser(User::class);

        $response = $this->handle(
            $form,
            $this->authFactory->createCollectionManagementContext($user, ($isCreation ? null : $form->collection)),
            $isCreation
        );

        if (!$response) {
            throw new NotFoundHttpException();
        }

        if ($request->query->get('simulate') !== 'true') {
            $this->createHandler->flush();
        }

        return new JsonFormattedResponse($response, $response->getHttpCode());
    }

    /**
     * @param $form
     * @param $auth
     * @param bool $isCreation
     *
     * @return null|CrudResponse
     *
     * @throws AuthenticationException
     */
    private function handle($form, $auth, bool $isCreation): ?CrudResponse
    {
        if ($isCreation) {
            return $this->createHandler->handle($form, $auth);
        }

        return $this->editHandler->handle($form, $auth);
    }
}
