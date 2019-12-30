<?php declare(strict_types=1);

namespace App\Controller;

use App\Domain\Authentication\Factory\IncomingTokenFactory;
use App\Domain\Common\Exception\AuthenticationException;
use App\Domain\Common\Exception\CommonValidationException;
use App\Domain\Common\Exception\ReadOnlyException;
use App\Domain\Common\Exception\RequestException;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Storage\Exception\StorageException;
use App\Infrastructure\Authentication\Token\TokenTransport;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ControllerTrait;

    protected function getParameter(string $name)
    {
        return $this->container->getParameter($name);
    }

    protected function getLoggedUserToken(?string $className = null)
    {
        /**
         * @var TokenTransport $sessionToken
         */
        $sessionToken = $this->get('security.token_storage')->getToken();
        $token = $sessionToken->getToken();

        if ($className) {
            return $this->get(IncomingTokenFactory::class)->createFromString($token->getId(), $className);
        }

        return $token;
    }

    protected function submitFormFromJsonRequest(Request $request, $formObject, string $formType): FormInterface
    {
        $arrayForm = \json_decode($request->getContent(), true);

        if (!\is_array($arrayForm)) {
            throw new RequestException('Missing content');
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

    public function createAPIReadOnlyResponse(): JsonResponse
    {
        return new JsonResponse(
            [
                'status'     => 'The API is read-only. Possibly due to primary-replica configuration.',
                'error_code' => 5000001,
                'http_code'  => 500
            ],
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public function createRequestExceptionResponse(RequestException $requestException): JsonResponse
    {
        return new JsonResponse(
            [
                'status' => $requestException->getMessage(),
                'error_code' => 400,
                'http_code'  => 400
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param callable $code
     *
     * @return Response
     */
    protected function withLongExecutionTimeAllowed(callable $code): Response
    {
        $previousValue = (int) ini_get('max_execution_time');
        set_time_limit($this->getLongExecutionTime());

        $return = $code();

        set_time_limit($previousValue);

        return $return;
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

        } catch (CommonValidationException $exception) {
            return new JsonResponse(
                [
                    'status' => 'Validation error',
                    'http_code' => 400,
                    'exit_code' => 400,
                    'fields' => $exception->getFields()
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );

        } catch (StorageException $storageException) {
            if ($storageException->getCode() === StorageException::codes['file_not_found']) {
                return $this->createNotFoundResponse('File not found in the storage');
            }

            throw $storageException;

        } catch (ReadOnlyException $exception) {
            return $this->createAPIReadOnlyResponse();
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
        $fixedDomain = $_ENV['APP_DOMAIN'] ?? '';

        return new BaseUrl($request->isSecure(), $fixedDomain ?: $request->getHttpHost());
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

    private function getLongExecutionTime(): int
    {
        $configured = getenv('LONG_EXECUTION_TIME');

        if (is_numeric($configured)) {
            return (int) $configured;
        }

        return 300;
    }
}
