<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserCreationHandler;
use App\Domain\Authentication\ActionHandler\UserEditHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AuthForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateEditUserController extends BaseController
{
    private UserCreationHandler $creationHandler;
    private UserEditHandler     $editHandler;
    private SecurityContextFactory $authFactory;

    public function __construct(UserCreationHandler $creationHandler, UserEditHandler $editHandler, SecurityContextFactory $authFactory)
    {
        $this->creationHandler = $creationHandler;
        $this->editHandler     = $editHandler;
        $this->authFactory     = $authFactory;
    }

    /**
     * Create a new user, assign permissions, set optional expiration, upload policy
     *
     * @param Request $request
     * @param string $userId
     *
     * @return JsonFormattedResponse
     *
     * @throws Exception
     */
    public function modifyAction(Request $request, string $userId = ''): Response
    {
        $isCreatingNewUser = $request->getMethod() === 'POST';

        /**
         * @var AuthForm $form
         */
        $form = $this->decodeRequestIntoDTO($request, AuthForm::class);
        $handler = $isCreatingNewUser ? $this->creationHandler : $this->editHandler;

        if ($userId && !$isCreatingNewUser) {
            $form->id = $userId;
        }

        return new JsonFormattedResponse(
            $handler->handle(
                $form,
                $this->authFactory->createFromUserAccount($this->getLoggedUser())
            ),
            JsonFormattedResponse::HTTP_CREATED
        );
    }
}
