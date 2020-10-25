<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\UserCreationHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AuthForm;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateUserController extends BaseController
{
    private UserCreationHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(UserCreationHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * Create a new user, assign roles, set optional expiration, upload policy
     *
     * @param Request $request
     *
     * @return JsonFormattedResponse
     *
     * @throws Exception
     */
    public function generateAction(Request $request): Response
    {
        /**
         * @var AuthForm
         */
        $form = $this->decodeRequestIntoDTO($request, AuthForm::class);

        return new JsonFormattedResponse(
            $this->handler->handle(
                $form,
                $this->authFactory->createFromUserAccount($this->getLoggedUser())
            ),
            JsonFormattedResponse::HTTP_CREATED
        );
    }
}
