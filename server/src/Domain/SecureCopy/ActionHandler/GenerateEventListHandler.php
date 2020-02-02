<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ActionHandler;

use App\Domain\SecureCopy\Collection\TimelinePartial;
use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Exception\ValidationException;
use App\Domain\SecureCopy\Factory\RepositoryLegendFactory;
use App\Domain\SecureCopy\Repository\FileRepository;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SubmitDataTypes;
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
     * @param string             $type
     *
     * @return string
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function handle(?DateTime $since, MirroringContext $context, int $limit, string $type): string
    {
        $this->assertHasRights($context);
        $this->assertLimitCanBeHandled($limit);

        $timeline = new TimelinePartial([], 0);

        // @todo: Support other types there eg. tokens
        if ($type === SubmitDataTypes::TYPE_FILE) {
            $timeline = $timeline->withMerged($this->repository->findFilesSince($since, $limit));
        }

        // headers first
        $output = \json_encode($this->repositoryLegendFactory->createLegend($timeline->count())->jsonSerialize(), JSON_THROW_ON_ERROR, 512) . "\n\n";

        // then body
        $output .= $timeline->toMultipleJsonDocuments();

        return $output;
    }

    /**
     * @param int $limit
     *
     * @throws ValidationException
     */
    private function assertLimitCanBeHandled(int $limit): void
    {
        if ($limit < 1) {
            throw new ValidationException('Invalid limit specified', 'limit');
        }

        // @todo: Extract limit into the configuration
        if ($limit >= 8192) {
            throw new ValidationException('Cannot serve more than 8192 entries', 'limit');
        }
    }
}
