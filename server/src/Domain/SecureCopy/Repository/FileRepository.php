<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Repository;

use App\Domain\SecureCopy\Collection\TimelinePartial;
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
    public function findFilesSince(?DateTime $since, int $limit = 1000): TimelinePartial;
}
