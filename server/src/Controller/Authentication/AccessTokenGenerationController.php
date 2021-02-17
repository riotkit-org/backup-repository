<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\AccessTokenGenerationHandler;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AccessTokenGenerationForm;
use App\Domain\Common\Exception\CommonValueException;
use App\Infrastructure\Common\Exception\JsonRequestException;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenGenerationController extends BaseController
{
    private AccessTokenGenerationHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(AccessTokenGenerationHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws AuthenticationException
     * @throws CommonValueException
     * @throws JsonRequestException
     */
    public function generateAction(Request $request): Response
    {
        /**
         * @var AccessTokenGenerationForm $form
         */
        $form = $this->decodeRequestIntoDTO($request, AccessTokenGenerationForm::class);

        return new JsonFormattedResponse(
            $this->handler->handle(
                $form,
                $this->authFactory->createFromUserAccount($this->getLoggedUser(User::class))
            ),
            JsonFormattedResponse::HTTP_OK
        );
    }
}
