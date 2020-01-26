<?php declare(strict_types=1);

namespace App\Domain\Replication\Repository;

use App\Domain\Replication\Collection\TimelinePartial;
use DateTime;

interface FileRepository
{
    /**
     * Finds all filenames and their checksums, timestamps SINCE selected date
     *
     * @param DateTime|null $since
     * @param int $limit
     *
     * @return TimelinePartial
     */
    public function findFilesToReplicateSince(?DateTime $since, int $limit = 1000): TimelinePartial;

    public function findExampleData(): TimelinePartial;
}
