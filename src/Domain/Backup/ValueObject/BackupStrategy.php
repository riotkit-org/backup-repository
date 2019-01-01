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
                'unknown_strategy_allowed___delete_oldest_when_adding_new___or__alert_when_backup_limit_reached'
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

    public function shouldCollectionRotateAutomatically(): bool
    {
        return $this->getValue() === self::STRATEGY_AUTO;
    }
}
