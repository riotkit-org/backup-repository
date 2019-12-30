<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Replication\Factory\RepositoryLegendFactory;
use App\Domain\Replication\Repository\FileRepository;
use App\Domain\Replication\Security\ReplicationContext;

/**
 * Generates a CSV formatted file with list of all files (optionally: with SINCE parameter)
 * Lines before "\n\n" are metadata header
 */
class GenerateReplicationListHandler extends BaseReplicationHandler
{
    /**
     * @var FileRepository $repository
     */
    private $repository;

    /**
     * @var RepositoryLegendFactory
     */
    private $repositoryLegendFactory;

    public function __construct(FileRepository $repository, RepositoryLegendFactory $factory)
    {
        $this->repository              = $repository;
        $this->repositoryLegendFactory = $factory;
    }

    public function handle(?\DateTime $since, BaseUrl $baseUrl, ReplicationContext $context): callable
    {
        $this->assertHasRights($context);

        $timeline = $this->repository->findFilesToReplicateSinceLazy($since);

        return function () use ($timeline, $baseUrl) {
            $out = fopen('php://output', 'wb');

            // write headers first
            fwrite($out, $this->repositoryLegendFactory->createLegend($baseUrl)->toCSV() . "\n\n");

            // then write the data
            $onEachChunkWrite = function () { flush(); }; // send response to the browser earlier if that's possible
            $data = $timeline->outputAsCSVOnStream($out, $onEachChunkWrite);
            $data();

            fclose($out);
        };
    }
}
