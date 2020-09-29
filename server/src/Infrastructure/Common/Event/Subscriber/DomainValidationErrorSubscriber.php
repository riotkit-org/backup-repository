<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Event\Subscriber;

use App\Domain\Common\Exception\DomainAssertionFailure;
use App\Infrastructure\Common\Http\ValidationErrorResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Catches all ValidationException and converts into ValidationErrorResponse
 * An automation for all controllers globally to not repeat try { } catch { } blocks on each controller
 */
class DomainValidationErrorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exc = $event->getThrowable();

        while ($exc instanceof HandlerFailedException && $exc->getPrevious()) {
            $exc = $exc->getPrevious();
        }

        if ($exc instanceof DomainAssertionFailure) {
            $event->setResponse(ValidationErrorResponse::createFromException($exc));
        }
    }
}
