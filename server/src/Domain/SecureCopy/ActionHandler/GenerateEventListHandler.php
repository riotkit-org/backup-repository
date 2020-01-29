<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ActionHandler;

use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Factory\RepositoryLegendFactory;
use App\Domain\SecureCopy\Repository\FileRepository;
use App\Domain\SecureCopy\Security\MirroringContext;
use DateTime;

/**
 * Generates a SecureCopy stream, so the mirroring application could fetch it as an INDEX of all items
 * and then start copying
 */
class GenerateEventListHandler extends BaseSecureCopyHandler
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
     * @param MirroringContext $context
     * @param int                $limit
     *
     * @return string
     *
     * @throws AuthenticationException
     */
    public function handle(?DateTime $since, MirroringContext $context, int $limit): string
    {
        $this->assertHasRights($context);

        $timeline = $this->repository->findFilesSince($since, $limit);

        // headers first
        $output = \json_encode($this->repositoryLegendFactory->createLegend($timeline->count())->jsonSerialize(), JSON_THROW_ON_ERROR, 512) . "\n\n";

        // then body
        $output .= $timeline->toMultipleJsonDocuments();

        return $output;
    }
}
