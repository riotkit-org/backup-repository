<?php declare(strict_types=1);

namespace App\Domain\Replication\Entity;

use App\Domain\SubmitDataTypes;
use DateTimeImmutable;
use function json_decode;
use function json_encode;

class ReplicationLogEntry
{
    public const STATUS_NOT_TAKEN = 0;
    public const STATUS_DONE      = 1;
    public const STATUS_ERROR     = 2;

    public const STATUSES = [
        self::STATUS_DONE,
        self::STATUS_NOT_TAKEN,
        self::STATUS_ERROR
    ];

    /**
     * Id
     *
     * @var string
     */
    private string $contentHash;

    private DateTimeImmutable $date;

    private DateTimeImmutable $queueUpdateDate;

    private string $timezone;

    /**
     * Real id of the original element
     *
     * @var string
     */
    private string $id;

    /**
     * Element type
     *
     * @see SubmitDataTypes
     *
     * @var string
     */
    private string $type;

    /**
     * JSON formatted SubmitForm
     *
     * @var string
     */
    private string $form;

    /**
     * Replication status
     *
     * @var int
     */
    private int $status = self::STATUS_NOT_TAKEN;

    public function createHash(): string
    {
        return \hash('sha256', $this->id . "\n" . $this->type);
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->date;
    }

    public static function createFromArray(array $data): self
    {
        $entry = new static();
        $entry->bumpModificationDate();
        $entry->date            = (new DateTimeImmutable())->setTimestamp($data['date']);
        $entry->timezone        = $data['tz']['timezone'];
        $entry->id              = $data['id'];
        $entry->type            = $data['type'];
        $entry->form            = json_encode($data['form'], JSON_THROW_ON_ERROR, 512);
        $entry->contentHash     = $entry->createHash();
        $entry->status          = self::STATUS_NOT_TAKEN;

        return $entry;
    }

    public function __toString(): string
    {
        return $this->type . '<' . $this->id . '>';
    }

    public function getContentHash(): string
    {
        return $this->contentHash;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getForm(): array
    {
        return json_decode($this->form, true, 512, JSON_THROW_ON_ERROR);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function markAsProcessed(): void
    {
        $this->status = self::STATUS_DONE;
        $this->bumpModificationDate();
    }

    public function markAsErrored(): void
    {
        $this->status = self::STATUS_ERROR;
        $this->bumpModificationDate();
    }

    private function bumpModificationDate(): void
    {
        $this->queueUpdateDate = new DateTimeImmutable();
    }
}
