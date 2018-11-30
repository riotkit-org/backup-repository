<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

class BackupStrategy
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
    private $value;

    public function __construct(string $strategy)
    {
        if (!\in_array($strategy, self::STRATEGIES, true)) {
            throw new \InvalidArgumentException('Unknown strategy "' . $strategy . '"');
        }

        $this->value = $strategy;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
