<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenGenerationHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use App\Domain\Authentication\Form\AuthForm;
use App\Infrastructure\Authentication\Form\AuthFormType;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GenerateTokenController extends BaseController
{
    private TokenGenerationHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(TokenGenerationHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function generateAction(Request $request): Response
    {
        $form = new AuthForm();
        $infrastructureForm = $this->submitFormFromJsonRequest($request, $form, AuthFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return $this->wrap(
            function () use ($form) {
                return new JsonResponse(
                    $this->handler->handle(
                        $form,
                        $this->authFactory->createFromToken($this->getLoggedUserToken())
                    ),
                    JsonResponse::HTTP_ACCEPTED
                );
            }
        );
    }
}
