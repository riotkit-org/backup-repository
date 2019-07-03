<?php declare(strict_types=1);

namespace App\Controller\Technical;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Lists all public routes
 */
class RestoreDBController extends AbstractController
{
    public function restoreAction(ContainerInterface $container): JsonResponse
    {
        $this->assertInDebugMode($container);

        if (strpos(($_SERVER['DATABASE_URL'] ?? ''), 'sqlite://') === false) {
            return new JsonResponse('Currently only SQLite3 database is supported in tests', 500);
        }

        $path = $this->getDbPath();

        if (\is_file($path . '.bak')) {
            copy($path . '.bak', $path);
            return new JsonResponse('OK, restored');
        }

        return new JsonResponse('OK, but no copy found');
    }

    public function backupAction(ContainerInterface $container): JsonResponse
    {
        $this->assertInDebugMode($container);

        if (strpos(($_SERVER['DATABASE_URL'] ?? ''), 'sqlite://') === false) {
            return new JsonResponse('Currently only SQLite3 database is supported in tests', 500);
        }

        $path = $this->getDbPath();
        copy($path, $path . '.bak');

        return new JsonResponse('OK, backup made.');
    }

    private function assertInDebugMode(ContainerInterface $container): void
    {
        if (!$container->getParameter('kernel.debug')) {
            throw new AccessDeniedHttpException();
        }
    }

    private function getDbPath(): string
    {
        $path = \trim(\explode('sqlite://', ($_SERVER['DATABASE_URL'] ?? ''))[1], '/');
        $path = \str_replace('%kernel.project_dir%', '../', $path);

        return \trim($path, '/');
    }
}
