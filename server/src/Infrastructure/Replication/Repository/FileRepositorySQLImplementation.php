<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Repository;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Replication\Collection\TimelinePartial;
use App\Domain\Replication\CompatibilityNames;
use App\Domain\Replication\DTO\StreamList\SubmitData;
use App\Domain\Replication\Repository\FileRepository;
use App\Domain\SubmitDataTypes;
use DateTime;
use Doctrine\DBAL\Connection;

/**
 * Plain SQL implementation of repository for performance reasons
 * The repository needs to be prepared to return millions of records
 *
 * The SQL statements should be compatible with basic SQL standard implemented by all engines
 * including PostgreSQL, SQLite3 and MySQL for sure.
 */
class FileRepositorySQLImplementation implements FileRepository
{
    private Connection $connection;
    private DomainBus $bus;

    public function __construct(Connection $connection, DomainBus $bus)
    {
        $this->connection = $connection;
        $this->bus        = $bus;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BusException
     */
    public function findFilesToReplicateSince(?DateTime $since, int $limit = 256): TimelinePartial
    {
        if (!$since) {
            $since = new DateTime('1990-01-01');
        }

        $rows = $this->connection->fetchAll(
            'SELECT fileName as filename
                 FROM file_registry
                 WHERE dateAdded > ?
                 ORDER BY dateAdded DESC
                 LIMIT ? OFFSET 0',
            [$since->format('Y-m-d H:i:s'), $limit]
        );

        $mapped = [];

        foreach ($rows as $row) {
            $mapped[] = $this->bus->callForFirstMatching(Bus::GET_ENTITY_SUBMIT_DATA, [
                'fileName' => $row['filename'],
                'type'     => SubmitDataTypes::TYPE_FILE
            ]);
        }

        return new TimelinePartial($mapped, $this->findMaxCount($since));
    }

    private function findMaxCount(?DateTime $since): int
    {
        $result = $this->connection->fetchColumn(
            'SELECT count(id) 
                 FROM file_registry
                 WHERE dateAdded >= ?',
            [$since->format('Y-m-d H:i:s')]
        );

        return (int) $result;
    }

    public function findExampleData(): TimelinePartial
    {
        $example = $this->bus->callForFirstMatching(Bus::GET_ENTITY_SUBMIT_DATA, [
            'fileName' => CompatibilityNames::EXAMPLE_FILE_NAME,
            'type'     => SubmitDataTypes::TYPE_FILE
        ]);

        return new TimelinePartial([$example], 1);
    }
}
