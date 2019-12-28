<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Replication\Repository\FileRepository;

class GenerateReplicationListHandler
{
    /**
     * @var FileRepository $repository
     */
    private $repository;

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(?\DateTime $since): callable
    {
        $timeline = $this->repository->findFilesToReplicateSinceLazy($since);

        return $timeline->outputAsCSVOnStream(fopen('php://output', 'wb'));
    }
}
