<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

use App\Domain\Backup\Exception\ValueObjectException;

class WebhookEventList
{
    /**
     * @var array
     */
    private $value;

    public const EVENT_BACKUP_ADDED  = 'backup.success';
    public const EVENT_BACKUP_FAILED = 'backup.failure';

    public const EVENTS = [
        self::EVENT_BACKUP_ADDED,
        self::EVENT_BACKUP_FAILED
    ];

    /**
     * @param array $events
     *
     * @throws ValueObjectException
     */
    public function __construct(array $events)
    {
        foreach ($events as $event) {
            if (!\in_array($event, self::EVENTS, true)) {
                throw new ValueObjectException('Unsupported event "' . $event . '"');
            }
        }

        $this->value = $events;
    }

    public function getValue(): array
    {
        return $this->value;
    }
}
