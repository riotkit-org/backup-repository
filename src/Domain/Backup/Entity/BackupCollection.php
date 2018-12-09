<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\Entity\Authentication\Token;
use App\Domain\Backup\ValueObject\BackupStrategy;
use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;
use App\Domain\Backup\ValueObject\Collection\Description;

/**
 * Represents a single backup (eg. database dump from iwa-ait.org website) collection
 */
class BackupCollection implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Description
     */
    protected $description;

    /**
     * @var CollectionLength
     */
    protected $maxBackupsCount;

    /**
     * @var BackupSize
     */
    protected $maxOneVersionSize;

    /**
     * @var CollectionSize
     */
    protected $maxCollectionSize;

    /**
     * @var Webhook[]
     */
    protected $webhooks;

    /**
     * @var Token[]
     */
    protected $allowedTokens;

    /**
     * Defines a strategy - should the older backups be deleted in favor of new backups, or should the user
     * delete backups manually and be alerted that the maximum is reached?
     *
     * @var BackupStrategy
     */
    protected $strategy;

    /**
     * @var \DateTimeImmutable
     */
    protected $creationDate;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->creationDate = new \DateTimeImmutable();
    }

    /**
     * @immutable
     *
     * @param CollectionLength $param
     *
     * @return BackupCollection
     */
    public function withMaxBackupsCount(CollectionLength $param): BackupCollection
    {
        $self = clone $this;
        $self->maxBackupsCount = $param;

        return $self;
    }

    /**
     * @immutable
     *
     * @param BackupSize $param
     *
     * @return BackupCollection
     */
    public function withOneVersionSize(BackupSize $param): BackupCollection
    {
        $self = clone $this;
        $self->maxOneVersionSize = $param;

        return $self;
    }

    /**
     * @immutable
     *
     * @param CollectionSize $param
     *
     * @return BackupCollection
     */
    public function withCollectionSize(CollectionSize $param): BackupCollection
    {
        $self = clone $this;
        $self->maxCollectionSize = $param;

        return $self;
    }

    /**
     * @immutable
     *
     * @param BackupStrategy $param
     *
     * @return BackupCollection
     */
    public function withStrategy(BackupStrategy $param): BackupCollection
    {
        $self = clone $this;
        $self->strategy = $param;

        return $self;
    }

    /**
     * @immutable
     *
     * @param Description $param
     *
     * @return BackupCollection
     */
    public function withDescription(Description $param): BackupCollection
    {
        $self = clone $this;
        $self->description = $param;

        return $self;
    }

    public function withTokenAdded(Token $token): BackupCollection
    {
        $self = clone $this;
        $self->allowedTokens[] = $token;

        return $self;
    }

    public function jsonSerialize()
    {
        return [
            'id'                          => $this->id,
            'max_backups_count'           => $this->maxBackupsCount,
            'max_one_backup_version_size' => $this->maxOneVersionSize,
            'max_collection_size'         => $this->maxCollectionSize,
            'created_at'                  => $this->creationDate,
            'strategy'                    => $this->strategy,
            'description'                 => $this->description
        ];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getMaxBackupsCount(): CollectionLength
    {
        return $this->maxBackupsCount;
    }

    public function getMaxOneVersionSize(): BackupSize
    {
        return $this->maxOneVersionSize;
    }

    public function getMaxCollectionSize(): CollectionSize
    {
        return $this->maxCollectionSize;
    }

    /**
     * @return bool
     */
    public function isTokenIdAllowed(string $tokenId): bool
    {
        foreach ($this->allowedTokens as $token) {
            if ($token->getId() === $tokenId) {
                return true;
            }
        }

        return false;
    }

    public function isSameAsCollection(BackupCollection $collection): bool
    {
        return $collection->getId() === $this->getId();
    }
}
