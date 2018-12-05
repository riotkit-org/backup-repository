<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

use App\Domain\Backup\Exception\ValueObjectException;

class BackupStrategy implements \JsonSerializable
{
    public const STRATEGY_AUTO = 'delete_oldest_when_adding_new';
    public const STRATEGY_MANUAL = 'alert_when_backup_limit_reached';

    public const STRATEGIES = [
        self::STRATEGY_AUTO,
        self::STRATEGY_MANUAL
    ];

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $strategy
     *
     * @throws ValueObjectException
     */
    public function __construct(string $strategy)
    {
        if (!\in_array($strategy, self::STRATEGIES, true)) {
            throw new ValueObjectException(
                'Unknown strategy "' . $strategy . '". Allowed: ' . implode(', ', self::STRATEGIES)
            );
        }

        $this->value = $strategy;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
