<?php declare(strict_types=1);

namespace App\Controller;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends Controller
{
    protected function getLoggedUserToken(): Token
    {
        /**
         * @var TokenTransport $sessionToken
         */
        $sessionToken = $this->get('security.token_storage')->getToken();

        return $sessionToken->getToken();
    }

    protected function submitFormFromJsonRequest(Request $request, $formObject, string $formType): FormInterface
    {
        $arrayForm = \json_decode($request->getContent(), true);

        if (!\is_array($arrayForm)) {
            throw new \InvalidArgumentException('Missing content');
        }

        $infrastructureForm = $this->createForm($formType, $formObject);
        $infrastructureForm->submit($arrayForm);

        return $infrastructureForm;
    }

    protected function submitFormFromRequestQuery(Request $request, $formObject, string $formType): FormInterface
    {
        $infrastructureForm = $this->createForm($formType, $formObject);
        $infrastructureForm->submit($request->query->all());

        return $infrastructureForm;
    }

    protected function createValidationErrorResponse(FormInterface $form): JsonResponse
    {
        return new JsonResponse(
            $this->collectErrorsForForm($form, 'form', []),
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    protected function createAccessDeniedResponse(): JsonResponse
    {
        return new JsonResponse('Not authorized', JsonResponse::HTTP_FORBIDDEN);
    }

    protected function wrap(callable $code): Response
    {
        try {
            return $code();

        } catch (AuthenticationException $exception) {
            return $this->createAccessDeniedResponse();
        }
    }

    protected function createBaseUrl(Request $request): BaseUrl
    {
        return new BaseUrl($request->isSecure(), $request->getHttpHost());
    }

    private function collectErrorsForForm(FormInterface $form, string $inputName, array $errors = []): array
    {
        $errorsForField = [];

        foreach ($form->getErrors() as $error) {
            $errorsForField[] = $error->getMessage();
        }

        if (\count($errorsForField) > 0) {
            $errors[$inputName] = $errorsForField;
        }

        // children fields
        if ($form->count()) {
            $compound = [];

            foreach ($form as $child) {
                $compound[] = $this->collectErrorsForForm($child, $inputName . '.' . $child->getName(), $errors);
            }

            if ($compound) {
                $errors = array_merge($errors, ...$compound);
            }
        }

        return $errors;
    }
}
