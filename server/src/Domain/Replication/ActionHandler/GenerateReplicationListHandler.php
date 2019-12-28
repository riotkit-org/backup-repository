<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Replication\Factory\RepositoryLegendFactory;
use App\Domain\Replication\Repository\FileRepository;

class GenerateReplicationListHandler
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

    public function handle(?\DateTime $since, BaseUrl $baseUrl): callable
    {
        $timeline = $this->repository->findFilesToReplicateSinceLazy($since);

        return function () use ($timeline, $baseUrl) {
            $out = fopen('php://output', 'wb');

            // write headers first
            fwrite($out, $this->repositoryLegendFactory->createLegend($baseUrl)->toCSV() . "\n\n");

            // then write the data
            $data = $timeline->outputAsCSVOnStream($out);
            $data();

            fclose($out);
        };
    }
}
