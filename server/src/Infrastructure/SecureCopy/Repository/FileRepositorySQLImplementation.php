<?php declare(strict_types=1);

namespace App\Infrastructure\SecureCopy\Repository;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\SecureCopy\Collection\TimelinePartial;
use App\Domain\SecureCopy\CompatibilityNames;
use App\Domain\SecureCopy\DTO\StreamList\SubmitData;
use App\Domain\SecureCopy\Repository\FileRepository;
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
    public function findFilesSince(?DateTime $since, int $limit = 256): TimelinePartial
    {
        if (!$since) {
            $since = new DateTime('1990-01-01');
        }

        $rows = $this->connection->fetchAll('SELECT fileName as filename FROM file_registry WHERE dateAdded > ? ORDER BY dateAdded DESC LIMIT ' . $limit . ' OFFSET 0',
            [$since->format('Y-m-d H:i:s')]
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
}
