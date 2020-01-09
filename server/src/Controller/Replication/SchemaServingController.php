<?php declare(strict_types=1);

namespace App\Controller\Replication;

use App\Controller\BaseController;
use App\Domain\Replication\ActionHandler\ServeSchemaHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SchemaServingController extends BaseController
{
    /**
     * @var ServeSchemaHandler
     */
    private $handler;

    public function __construct(ServeSchemaHandler $handler)
    {
        $this->handler = $handler;
    }

    public function dumpSchemaAction(string $name): Response
    {
        return $this->wrap(function () use ($name) {
            return new JsonResponse($this->handler->handle($name));
        });
    }
}
