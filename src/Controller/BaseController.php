<?php declare(strict_types=1);

namespace App\Controller;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\ValidationException;
use App\Domain\Common\Exception\AuthenticationException;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Storage\Exception\StorageException;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractController
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
        $infrastructureForm->submit(
            \array_merge($request->query->all(), $request->request->all())
        );

        return $infrastructureForm;
    }

    protected function createValidationErrorResponse(FormInterface $form): JsonResponse
    {
        return new JsonResponse(
            [
                'status' => 'Validation error',
                'http_code' => 400,
                'exit_code' => 400,
                'fields' => $this->collectErrorsForForm($form, 'form', [])
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    protected function createAccessDeniedResponse($message = 'Forbidden'): JsonResponse
    {
        return new JsonResponse(
            [
                'status'     => $message,
                'error_code' => 403,
                'http_code'  => 403
            ],
            JsonResponse::HTTP_FORBIDDEN
        );
    }

    protected function createNotFoundResponse(string $message = 'Not found'): JsonResponse
    {
        return new JsonResponse(
            [
                'status'     => $message,
                'error_code' => 404,
                'http_code'  => 404
            ],
            JsonResponse::HTTP_NOT_FOUND
        );
    }

    /**
     * @param callable $code
     *
     * @return Response
     *
     * @throws \Exception
     */
    protected function wrap(callable $code): Response
    {
        try {
            return $code();

        } catch (AuthenticationException $exception) {
            return $this->createAccessDeniedResponse($exception->getMessage());

        } catch (ValidationException $exception) {
            return new JsonResponse(
                [
                    'status' => 'Validation error',
                    'http_code' => 400,
                    'exit_code' => 400,
                    'fields' => $exception->getFields()
                ]
            );

        } catch (StorageException $storageException) {
            if ($storageException->getCode() === StorageException::codes['file_not_found']) {
                return $this->createNotFoundResponse('File not found in the storage');
            }

            throw $storageException;
        }
    }

    protected function toBoolean($value, bool $onNull): bool
    {
        if (\is_numeric($value)) {
            $value = (int) $value;

            return $value > 0;
        }

        if (\is_string($value)) {
            $normalized = \strtolower(\trim($value));

            return $normalized === 'true' || $normalized === 'yes';
        }

        if (\is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return $onNull;
        }

        throw new \InvalidArgumentException('Invalid data type "' . \gettype($value) . '" specified, cannot parse boolean');
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
