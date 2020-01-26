<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler\Server;

use App\Domain\Replication\ActionHandler\BaseReplicationHandler;
use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Factory\RepositoryLegendFactory;
use App\Domain\Replication\Repository\FileRepository;
use App\Domain\Replication\Security\ReplicationContext;
use DateTime;

/**
 * Generates a replication stream, so the replicas could fetch it as an INDEX of all items to replicate
 * and then start downloading items by workers
 */
class GenerateReplicationListHandler extends BaseReplicationHandler
{
    private FileRepository $repository;
    private RepositoryLegendFactory $repositoryLegendFactory;

    public function __construct(FileRepository $repository, RepositoryLegendFactory $factory)
    {
        $this->repository              = $repository;
        $this->repositoryLegendFactory = $factory;
    }

    /**
     * @param DateTime|null      $since
     * @param ReplicationContext $context
     * @param int                $limit
     *
     * @return string
     *
     * @throws AuthenticationException
     */
    public function handle(?DateTime $since, ReplicationContext $context, int $limit, bool $shouldReturnExampleData): string
    {
        $this->assertHasRights($context);

        $timeline = $shouldReturnExampleData ? $this->repository->findExampleData() : $this->repository->findFilesToReplicateSince($since, $limit);

        // headers first
        $output = \json_encode($this->repositoryLegendFactory->createLegend($timeline->count())->jsonSerialize(), JSON_THROW_ON_ERROR, 512) . "\n\n";

        // then body
        $output .= $timeline->toMultipleJsonDocuments();

        return $output;
    }
}
