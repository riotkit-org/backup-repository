<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorsSubscriber implements EventSubscriberInterface
{
    private bool $isDevEnvironment;

    public function __construct(bool $isDev)
    {
        $this->isDevEnvironment = $isDev;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999],
            KernelEvents::RESPONSE => ['onKernelResponse', 9999],
            KernelEvents::EXCEPTION => ['onKernelException', 9999],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->isDevEnvironment) {
            return;
        }

        $response = $event->getResponse();
        if ($response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH');
            $response->headers->set('Access-Control-Allow-Headers', 'content-type,authorization');
        }
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->isDevEnvironment) {
            return;
        }

        // Don't do anything if it's not the master request.
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $method = $request->getRealMethod();

        if (Request::METHOD_OPTIONS === $method) {
            $response = new Response();
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->isDevEnvironment) {
            return;
        }

        // Don't do anything if it's not the master request.
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        if ($response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH');
            $response->headers->set('Access-Control-Allow-Headers', 'content-type,authorization');
        }
    }
}
