<?php declare(strict_types=1);

namespace Tests\Infrastructure\Common\Event\Subscriber;

use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Common\Exception\ApplicationException;
use App\Domain\Common\Exception\DomainAssertionFailure;
use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Common\ValueObject\DiskSpace;
use App\Domain\Storage\Exception\FileRetrievalError;
use App\Infrastructure\Common\Event\Subscriber\ErrorFormattingSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\BaseTestCase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @see ErrorFormattingSubscriber
 */
class ErrorFormattingSubscriberTest extends BaseTestCase
{
    use ArraySubsetAsserts;

    public function provideVariousFailuresExamples(): array
    {
        return [
            'BackupLogicException' => [
                '$exception'              => BackupLogicException::createFromCollectionTooSmallCause(new DiskSpace(161)),
                '$expectResponseContains' => [
                    'error' => 'Collection maximum size is too small, requires at least 161B',
                    'type'  => 'validation.error'
                ],
                '$expectsResponseCode'    => 400,
                '$isAppDebugMode'         => false,
                '$appEnvironmentType'     => 'prod'
            ],

            'DomainAssertionFailure' => [
                '$exception'              => DomainAssertionFailure::fromErrors([
                    DomainInputValidationConstraintViolatedError::fromString(
                        'strategy', 'Bakunin had right', 400
                    )
                ]),
                '$expectResponseContains' => [
                    'error'  => 'JSON payload validation error',
                    'fields' => [
                        'strategy' => [
                            'message' => 'Bakunin had right',
                            'code'    => 400
                        ]
                    ]
                ],
                '$expectsResponseCode'    => 400,
                '$isAppDebugMode'         => false,
                '$appEnvironmentType'     => 'prod'
            ],

            'FileRetrievalError extends ApplicationException' => [
                // FileRetrievalError extends ApplicationException
                '$exception'              => FileRetrievalError::fromChunkedTransferNotSupported(),
                '$expectResponseContains' => [
                    'error' => '"Transfer-Encoding: Chunked" type uploads are not supported. Use a reverse proxy like NGINX with request buffering',
                    'type'  => 'app.fatal-error'
                ],
                '$expectsResponseCode'    => 400,
                '$isAppDebugMode'         => false,
                '$appEnvironmentType'     => 'prod'
            ],

            'Raw ApplicationException' => [
                '$exception'              => new ApplicationException(),
                '$expectResponseContains' => [
                    'error' => 'Internal server error',
                    'type'  => 'app.fatal-error'
                ],
                '$expectsResponseCode'    => 500,
                '$isAppDebugMode'         => false,
                '$appEnvironmentType'     => 'prod'
            ],

            '404 with NotFoundHttpException on prod' => [
                '$exception'              => new NotFoundHttpException(),
                '$expectResponseContains' => [
                    'error' => 'Route or resource not found',
                    'type'  => 'app.not-found'
                ],
                '$expectsResponseCode'    => 404,
                '$isAppDebugMode'         => false,
                '$appEnvironmentType'     => 'prod'
            ],

            '404 with NotFoundHttpException on dev' => [
                '$exception'              => new NotFoundHttpException(),
                '$expectResponseContains' => 'Default Symfony response in dev/test mode',
                '$expectsResponseCode'    => 499, // the status is unknown, mocked, not checked in dev/test mode
                '$isAppDebugMode'         => true,
                '$appEnvironmentType'     => 'dev'
            ],

            '404 with NotFoundHttpException on test' => [
                '$exception'              => new NotFoundHttpException(),
                '$expectResponseContains' => [
                    'error' => 'Route or resource not found',
                    'type'  => 'app.not-found'
                ],
                '$expectsResponseCode'    => 404,
                '$isAppDebugMode'         => true,
                '$appEnvironmentType'     => 'test'
            ],

            'AccessDeniedHttpException on dev' => [
                '$exception'              => new AccessDeniedHttpException(),
                '$expectResponseContains' => 'Default Symfony response in dev/test mode',
                '$expectsResponseCode'    => 499, // the status is unknown, mocked, not checked in dev/test mode
                '$isAppDebugMode'         => true,
                '$appEnvironmentType'     => 'dev'
            ],

            'AccessDeniedHttpException on prod' => [
                '$exception'              => new AccessDeniedHttpException(),
                '$expectResponseContains' => [
                    'error' => 'Access denied for given route or resource',
                    'code'  => 403,
                    'type'  => 'app.fatal-error'
                ],
                '$expectsResponseCode'    => 403, // the status is unknown, mocked, not checked in dev/test mode
                '$isAppDebugMode'         => false,
                '$appEnvironmentType'     => 'prod'
            ]
        ];
    }

    /**
     * @dataProvider provideVariousFailuresExamples
     *
     * @param \Throwable $exception
     * @param array|string $expectResponseContains
     * @param int $expectsResponseCode
     * @param bool $isAppDebugMode
     * @param string $appEnvironmentType
     *
     * @throws DomainInputValidationConstraintViolatedError
     *
     * @throws \Throwable
     */
    public function testVariousExceptionsAndApplicationModesHandling(
        \Throwable   $exception,
        array|string $expectResponseContains,
        int          $expectsResponseCode,
        bool         $isAppDebugMode,
        string       $appEnvironmentType
    ) {
        $formatter = new ErrorFormattingSubscriber(
            isDev: $isAppDebugMode,
            envName: $appEnvironmentType,
            logger: $this->createMock(LoggerInterface::class)
        );

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            1,
            $exception
        );

        $event->setResponse(new Response('Default Symfony response in dev/test mode', 499));

        $formatter->onKernelException($event);
        $response = json_decode($event->getResponse()->getContent(), true);

        if (is_array($response)) {
            $this->assertArraySubset($expectResponseContains, $response);

        } elseif ($response === null) {
            $this->assertStringContainsString($expectResponseContains, $event->getResponse()->getContent());

        } else {
            $this->assertStringContainsString($expectResponseContains, $response);
        }

        $this->assertEquals($expectsResponseCode, $event->getResponse()->getStatusCode());
    }
}
