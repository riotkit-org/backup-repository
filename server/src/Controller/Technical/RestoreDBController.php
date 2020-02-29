<?php declare(strict_types=1);

namespace App\Controller\Technical;

use App\Infrastructure\Common\Http\JsonFormattedResponse;
use App\Infrastructure\Common\Test\Database\RestoreDBInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Lists all public routes
 */
class RestoreDBController extends AbstractController
{
    private RestoreDBInterface $dbStateManager;

    public function __construct(RestoreDBInterface $dbStateManager)
    {
        $this->dbStateManager = $dbStateManager;
    }

    /**
     * Restore a database (only in test/dev mode)
     *
     * @param ContainerInterface $container
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function restoreAction(ContainerInterface $container): JsonResponse
    {
        $this->assertInDebugMode($container);

        if ($this->dbStateManager->restore()) {
            return new JsonResponse('OK, restored');
        }

        return new JsonFormattedResponse('OK, but nothing restored');
    }

    /**
     * Backup a database (only in test/dev mode)
     *
     * @param ContainerInterface $container
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function backupAction(ContainerInterface $container): JsonResponse
    {
        $this->assertInDebugMode($container);
        $this->dbStateManager->backup();

        return new JsonFormattedResponse('OK, backup made.');
    }

    private function assertInDebugMode(ContainerInterface $container): void
    {
        if (!$container->getParameter('kernel.debug')) {
            throw new AccessDeniedHttpException();
        }
    }
}
