<?php declare(strict_types=1);

namespace App\Domain\Replication\Entity\Authentication;

use App\Domain\Common\SharedEntity\Token as TokenFromCommon;
use App\Domain\Replication\Entity\ReplicationClient;

class Token extends TokenFromCommon
{
    /**
     * @var ReplicationClient
     */
    private $replicationClient;

    public function getReplicationClient(): ReplicationClient
    {
        return $this->replicationClient;
    }
}
