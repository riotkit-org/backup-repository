<?php declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Controller\BaseController;
use App\Domain\Authentication\ActionHandler\TokenGenerationHandler;
use App\Domain\Authentication\Form\AuthForm;
use App\Infrastructure\Authentication\Form\AuthFormType;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GenerateTokenController extends BaseController
{
    /**
     * @var TokenGenerationHandler
     */
    private $handler;

    public function __construct(TokenGenerationHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function generateAction(Request $request): JsonResponse
    {
        $form = new AuthForm();
        $infrastructureForm = $this->submitFormFromJsonRequest($request, $form, AuthFormType::class);

        if (!$infrastructureForm->isValid()) {
            return $this->createValidationErrorResponse($infrastructureForm);
        }

        return new JsonResponse(
            $this->handler->handle($form),
            JsonResponse::HTTP_ACCEPTED
        );
    }
}
