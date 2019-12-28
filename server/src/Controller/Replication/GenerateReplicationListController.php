<?php declare(strict_types=1);

namespace App\Controller\Replication;

use App\Controller\BaseController;
use App\Domain\Replication\ActionHandler\GenerateReplicationListHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerateReplicationListController extends BaseController
{
    /**
     * @var GenerateReplicationListHandler
     */
    private $handler;

    public function __construct(GenerateReplicationListHandler $handler)
    {
        $this->handler = $handler;
    }

    public function dumpAction(\DateTime $since = null): Response
    {
        $csvStream = $this->handler->handle($since);

        return new StreamedResponse($csvStream, Response::HTTP_OK, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="replication-list.csv"'
        ]);
    }
}
