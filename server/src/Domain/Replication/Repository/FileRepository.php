<?php declare(strict_types=1);

namespace App\Domain\Replication\Repository;

use App\Domain\Replication\Collection\TimelinePartial;

interface FileRepository
{
    /**
     * Finds all filenames and their checksums, timestamps SINCE selected date
     *
     * @param \DateTime|null $since
     *
     * @return array
     */
    public function findFilesToReplicateSince(?\DateTime $since, ?int $page = null, int $buffer = 1000): array;

    /**
     * Lazy version of findFilesToReplicateSince()
     * Fetches records paginated, so those can be fetched & printed to stdout & removed from memory on the fly
     * by other layers
     *
     * @param \DateTime|null $since
     *
     * @param int $buffer
     * @return TimelinePartial
     */
    public function findFilesToReplicateSinceLazy(?\DateTime $since = null, int $buffer = 1000): TimelinePartial;
}
