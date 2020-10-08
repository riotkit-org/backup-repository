<?php declare(strict_types=1);

namespace App\Controller;

use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Factory\IncomingUserFactory;
use App\Domain\Common\Exception\AuthenticationException;
use App\Domain\Common\Exception\CommonValidationException;
use App\Domain\Common\Exception\ReadOnlyException;
use App\Domain\Common\Exception\RequestException;
use App\Domain\Storage\Exception\StorageException;
use App\Infrastructure\Authentication\Token\TokenTransport;
use App\Infrastructure\Common\Exception\JsonRequestException;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class BaseController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ControllerTrait; // @todo: Remove usage of ControllerTrait due to deprecation

    protected function getParameter(string $name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * @param Request $request
     * @param string $DTOClassName
     *
     * @return ${DTOClassName}
     *
     * @throws JsonRequestException
     */
    protected function decodeRequestIntoDTO(Request $request, string $DTOClassName)
    {
        try {
            return $this->container->get('jms_serializer.public')
                ->deserialize($request->getContent(false), $DTOClassName, 'json');
        } catch (\ErrorException $exception) {
            throw JsonRequestException::fromJsonToFormMappingError($exception);
        }
    }

    protected function getLoggedUser(?string $className = null)
    {
        /**
         * @var TokenTransport $sessionToken
         */
        $sessionToken = $this->get('security.token_storage')->getToken();

        if (!$sessionToken || !$sessionToken->getUser() || !$sessionToken->getUser()->getId()) {
            throw new AccessDeniedHttpException('No active token found');
        }

        if ($className) {
            return $this->get(IncomingUserFactory::class)->createFromString($sessionToken->getUser()->getId(), $className);
        }

        return $sessionToken->getUser();
    }

    protected function getLoggedUserOrAnonymousToken(?string $className = null)
    {
        try {
            return $this->getLoggedUser($className);

        } catch (AccessDeniedHttpException $exception) {
            return User::createAnonymousToken();
        }
    }

    // @todo: https://github.com/riotkit-org/file-repository/issues/114
    protected function submitFormFromJsonRequest(Request $request, $formObject, string $formType): FormInterface
    {
        $arrayForm = \json_decode($request->getContent(), true);

        if (!\is_array($arrayForm)) {
            throw new RequestException('Missing content');
        }

        // dirty fix: normalize attributes form, allow array submission as part of JSON payload
        if (isset($arrayForm['attributes']) && \is_array($arrayForm['attributes'])) {
            $arrayForm['attributes'] = json_encode($arrayForm['attributes']);
        }

        $infrastructureForm = $this->createForm($formType, $formObject);
        $infrastructureForm->submit($arrayForm);

        return $infrastructureForm;
    }

    // @todo: https://github.com/riotkit-org/file-repository/issues/114
    protected function submitFormFromRequestQuery(Request $request, $formObject, string $formType): FormInterface
    {
        $infrastructureForm = $this->createForm($formType, $formObject);
        $infrastructureForm->submit(
            \array_merge($request->query->all(), $request->request->all())
        );

        return $infrastructureForm;
    }

    // @todo: https://github.com/riotkit-org/file-repository/issues/115
    protected function createValidationErrorResponse(FormInterface $form): JsonFormattedResponse
    {
        return new JsonFormattedResponse(
            [
                'status' => 'Validation error',
                'http_code' => 400,
                'exit_code' => 400,
                'fields' => $this->collectErrorsForForm($form, 'form', [])
            ],
            JsonFormattedResponse::HTTP_BAD_REQUEST
        );
    }

    // @todo: https://github.com/riotkit-org/file-repository/issues/115
    protected function createAccessDeniedResponse($message = 'Forbidden'): JsonFormattedResponse
    {
        return new JsonFormattedResponse(
            [
                'status'     => $message,
                'error_code' => 403,
                'http_code'  => 403
            ],
            JsonFormattedResponse::HTTP_FORBIDDEN
        );
    }

    // @todo: https://github.com/riotkit-org/file-repository/issues/115
    protected function createNotFoundResponse(string $message = 'Not found'): JsonFormattedResponse
    {
        return new JsonFormattedResponse(
            [
                'status'     => $message,
                'error_code' => 404,
                'http_code'  => 404
            ],
            JsonFormattedResponse::HTTP_NOT_FOUND
        );
    }

    // @todo: https://github.com/riotkit-org/file-repository/issues/115
    public function createAPIReadOnlyResponse(): JsonFormattedResponse
    {
        return new JsonFormattedResponse(
            [
                'status'     => 'The API is read-only. Possibly due to primary-replica configuration.',
                'error_code' => 5000001,
                'http_code'  => 500
            ],
            JsonFormattedResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    // @todo: https://github.com/riotkit-org/file-repository/issues/115
    public function createRequestExceptionResponse(RequestException $requestException): JsonFormattedResponse
    {
        return new JsonFormattedResponse(
            [
                'status' => $requestException->getMessage(),
                'error_code' => 400,
                'http_code'  => 400
            ],
            JsonFormattedResponse::HTTP_BAD_REQUEST
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
            return new JsonFormattedResponse(
                [
                    'status' => 'Validation error',
                    'http_code' => 400,
                    'exit_code' => 400,
                    'fields' => $exception->getFields()
                ],
                JsonFormattedResponse::HTTP_BAD_REQUEST
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

    // @todo: https://github.com/riotkit-org/file-repository/issues/114
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
