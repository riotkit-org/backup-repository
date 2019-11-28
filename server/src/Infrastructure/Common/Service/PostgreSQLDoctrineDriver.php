<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Driver\PDOPgSql\Driver;
use \PDO;
use \PDOException;
use function defined;

/**
 * Driver that connects through pdo_pgsql
 *
 * Forked original PostgreSQL driver, extended to have a possibility to pass raw PDO DSN to be
 * able to connect via Unix socket
 *
 * @codeCoverageIgnore
 */
class PostgreSQLDoctrineDriver extends Driver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        $dsn = trim($_SERVER['POSTGRES_DB_PDO_DSN'] ?? '', '\'" ');

        try {
            $pdo = new PDOConnection(
                $dsn,
                $_SERVER['POSTGRES_DB_PDO_ROLE'] ?? '',
                $password,
                $driverOptions
            );

            if (defined('PDO::PGSQL_ATTR_DISABLE_PREPARES')
                && (! isset($driverOptions[PDO::PGSQL_ATTR_DISABLE_PREPARES])
                    || $driverOptions[PDO::PGSQL_ATTR_DISABLE_PREPARES] === true
                )
            ) {
                $pdo->setAttribute(PDO::PGSQL_ATTR_DISABLE_PREPARES, true);
            }

            /* defining client_encoding via SET NAMES to avoid inconsistent DSN support
             * - the 'client_encoding' connection param only works with postgres >= 9.1
             * - passing client_encoding via the 'options' param breaks pgbouncer support
             */
            if (isset($params['charset'])) {
                $pdo->exec('SET NAMES \'' . $params['charset'] . '\'');
            }

            return $pdo;
        } catch (PDOException $e) {
            throw DBALException::driverException($this, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_pgsql';
    }
}
