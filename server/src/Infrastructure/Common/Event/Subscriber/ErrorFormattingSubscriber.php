<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Event\Subscriber;

use App\Domain\Common\Exception\ApplicationException;
use App\Domain\Common\Exception\DomainAssertionFailure;
use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Infrastructure\Common\Exception\HttpError;
use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Common\Http\ValidationErrorResponse;
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
    private bool $isDevEnvironment;

    public function __construct(bool $isDev)
    {
        $this->isDevEnvironment = $isDev;
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
            $event->setResponse(
                $this->postProcessResponse(ValidationErrorResponse::createFromException($exc), $exc)
            );

            return;

        } elseif ($exc instanceof DomainInputValidationConstraintViolatedError) {
            throw $exc;

        } elseif ($exc instanceof ApplicationException) {
            $event->setResponse(
                $this->postProcessResponse(new JsonFormattedResponse($exc->jsonSerialize(), $exc->getHttpCode()), $exc)
            );

            return;
        }

        //
        // Infrastructure / routing / http / internal errors
        // (Are formatted only on PROD environment. On dev/test are shown as raised exception for debugging)
        //

        if (!$this->isDevEnvironment) {
            if ($exc instanceof NotFoundHttpException) {
                $event->setResponse(
                    new JsonFormattedResponse(HttpError::fromInternalServerError()->jsonSerialize(), 404)
                );

                return;
            } elseif ($exc instanceof AccessDeniedHttpException) {
                $event->setResponse(
                    new JsonFormattedResponse(HttpError::fromAccessDeniedError()->jsonSerialize(), 403)
                );

                return;
            }

            // default formatting for 500 error
            $event->setResponse(new JsonFormattedResponse(HttpError::fromInternalServerError()->jsonSerialize()));
        }
    }

    private function postProcessResponse(JsonFormattedResponse $response, ApplicationException $exception): JsonFormattedResponse
    {
        if (!$exception->canBeDisplayedPublic() && !$this->isDevEnvironment) {
            return new JsonFormattedResponse(HttpError::fromInternalServerError()->jsonSerialize());
        }

        if ($this->isDevEnvironment) {
            $json = json_decode($response->getContent(), true);
            $json['_exc'] = $exception->getTrace();

            $response->setContent(json_encode($json, JSON_PRETTY_PRINT));
        }

        return $response;
    }
}
