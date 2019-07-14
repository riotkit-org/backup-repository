<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

use Doctrine\DBAL\Connection;

class ORMConnectionCheck
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function test(): bool
    {
        return $this->connection->fetchColumn('SELECT 256;') === '256';
    }
}
