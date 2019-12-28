<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Repository;

use App\Domain\Replication\Collection\TimelinePartial;
use App\Domain\Replication\DTO\File;
use App\Domain\Replication\Repository\FileRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints\Time;

/**
 * Plain SQL implementation of repository for performance reasons
 * The repository needs to be prepared to return millions of records
 *
 * The SQL statements should be compatible with basic SQL standard implemented by all engines
 * including PostgreSQL, SQLite3 and MySQL for sure.
 */
class FileRepositorySQLImplementation implements FileRepository
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @{inheritdoc}
     */
    public function findFilesToReplicateSince(?\DateTime $since, ?int $page = null, int $buffer = 1000): array
    {
        if (!$since) {
            $since = new \DateTime('1990-01-01');
        }

        $sql = '';
        $params = [$since->format('Y-m-d H:i:s')];

        if ($page !== null) {
            $sql = ' LIMIT ?, ?';
            $params[] = ($page - 1) * $buffer;
            $params[] = $buffer;
        }

        $rows = $this->connection->fetchAll(
            'SELECT fileName, contentHash, dateAdded, id 
                 FROM file_registry
                 WHERE dateAdded >= ?
                 ORDER BY dateAdded DESC 
                 ' . $sql,
            $params
        );

        $mapped = [];

        foreach ($rows as $row) {
            $mapped[] = new File($row['fileName'], $row['dateAdded'], $row['contentHash']);
        }

        return $mapped;
    }

    public function findFilesToReplicateSinceLazy(?\DateTime $since = null, int $buffer = 1000): TimelinePartial
    {
        if (!$since) {
            $since = new \DateTime('1990-01-01');
        }

        $maxCount = $this->findMaxCount($since);
        $iterations = ceil($maxCount / $buffer);

        /**
         * @var callable[] $callbacks
         */
        $callbacks = [];

        for ($currentIter = 1; $currentIter <= $iterations; $currentIter++) {
            $callbacks[] = function () use ($since, $currentIter, $buffer) {
                return $this->findFilesToReplicateSince($since, $currentIter, $buffer);
            };
        }

        return new TimelinePartial($callbacks, $maxCount);
    }

    private function findMaxCount(?\DateTime $since): int
    {
        $result = $this->connection->fetchColumn(
            'SELECT count(id) 
                 FROM file_registry
                 WHERE dateAdded >= ?',
            [$since->format('Y-m-d H:i:s')]
        );

        return (int) $result;
    }
}
