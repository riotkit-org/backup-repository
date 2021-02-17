<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

use App\Domain\Common\Exception\DatabaseException as CommonDatabaseException;
use Throwable;

class DatabaseException extends CommonDatabaseException
{
    private ?string $sqlState;
    private ?string $driverErrorCode;

    public function __construct(string $message, int $code, ?string $sqlState, ?string $driverErrorCode, Throwable $previous = null)
    {
        $this->sqlState = $sqlState;
        $this->driverErrorCode = $driverErrorCode;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string|null
     */
    public function getSqlState(): ?string
    {
        return $this->sqlState;
    }

    /**
     * @return string|null
     */
    public function getDriverErrorCode(): ?string
    {
        return $this->driverErrorCode;
    }
}
