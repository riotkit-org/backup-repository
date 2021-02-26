<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Event\Subscriber;

use App\Domain\Common\Exception\ApplicationException;
use App\Domain\Common\Exception\DomainAssertionFailure;
use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Common\Exception\ResourceNotFoundException;
use App\Infrastructure\Common\Exception\HttpError;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Common\Http\ValidationErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Catches all ValidationException and converts into ValidationErrorResponse
 * An automation for all controllers globally to not repeat try { } catch { } blocks on each controller
 *
 * Additionally - when Symfony catches an error and passes to our subscriber, then this error is tracked and showed
 *                in developer tools
 */
class ErrorFormattingSubscriber implements EventSubscriberInterface
{
    private bool $isDebugEnvironment;
    private bool $isTestEnvironment;
    private LoggerInterface $logger;

    public function __construct(bool $isDev, string $envName, LoggerInterface $logger)
    {
        $this->isDebugEnvironment = $isDev;
        $this->isTestEnvironment  = $envName === 'test';
        $this->logger             = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exc = $event->getThrowable();

        // get real exception
        while ($exc instanceof HandlerFailedException && $exc->getPrevious()) {
            $exc = $exc->getPrevious();
        }

        //
        // Logic Errors
        // (Are formatted always to JSON regardless of dev/test/prod environment)
        //

        if ($exc instanceof DomainAssertionFailure) {
            $this->logger->debug($exc);

            $event->setResponse(
                $this->postProcessResponse(ValidationErrorResponse::createFromException($exc), $exc)
            );

            return;

        } elseif ($exc instanceof DomainInputValidationConstraintViolatedError) {
            $this->logger->debug($exc);

            throw $exc;

        } elseif ($exc instanceof ApplicationException) {
            $event->setResponse(
                $this->postProcessResponse(new JsonFormattedResponse($exc->jsonSerialize(), $exc->getHttpCode()), $exc)
            );

            $this->logger->error($exc);
            return;
        }

        //
        // Infrastructure / routing / http / internal errors
        // (Are formatted only on PROD environment. On dev/test are shown as raised exception for debugging)
        //
        if (!$this->isDebugEnvironment || $this->isTestEnvironment) {
            if ($exc instanceof NotFoundHttpException || $exc instanceof ResourceNotFoundException) {
                $httpError = $exc instanceof ResourceNotFoundException ? HttpError::fromNotFoundError($exc->getMessage()) : HttpError::fromNotFoundError();

                $event->setResponse(
                    $this->postProcessResponse(new JsonFormattedResponse($httpError->jsonSerialize(), 404), $exc)
                );

                return;
            } elseif ($exc instanceof AccessDeniedHttpException) {
                $event->setResponse(
                    $this->postProcessResponse(new JsonFormattedResponse(HttpError::fromAccessDeniedError()->jsonSerialize(), 403), $exc)
                );

                return;
            }

            // default formatting for 500 error
            $this->logger->critical($exc);
            $event->setResponse(new JsonFormattedResponse(HttpError::fromInternalServerError()->jsonSerialize()));
        }
    }

    private function postProcessResponse(JsonFormattedResponse $response, \Exception $exception): JsonFormattedResponse
    {
        $canBeDisplayedPublic = $exception instanceof ApplicationException && $exception->canBeDisplayedPublic();

        if (!$canBeDisplayedPublic && !$this->isDebugEnvironment) {
            return new JsonFormattedResponse(HttpError::fromInternalServerError()->jsonSerialize());
        }

        if ($this->isDebugEnvironment) {
            $json = json_decode($response->getContent(), true);
            $json['_exc'] = $exception->getTrace();

            $response->setContent(json_encode($json, JSON_PRETTY_PRINT));
        }

        return $response;
    }
}
