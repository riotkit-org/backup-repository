<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\ValueObject\BackupStrategy;
use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;
use App\Domain\Backup\ValueObject\Collection\Description;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\Password;
use App\Domain\Common\ValueObject\DiskSpace;

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
     * @var Password
     */
    protected $password;

    /**
     * @var Filename
     */
    protected $filename;

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
     * @var User[]
     */
    protected $allowedTokens = [];

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
     * Be careful with this method, should be used only on creation
     *
     * @param string $id
     *
     * @return BackupCollection
     */
    public function changeId(string $id): BackupCollection
    {
        $this->id = $id;

        return $this;
    }

    public function withAnonymousData(): BackupCollection
    {
        $clone                = clone $this;
        $clone->id            = null;
        $clone->description   = 'Anonymous';
        $clone->allowedTokens = [];

        return $clone;
    }

    /**
     * Immutable
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
     * Immutable
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
     * Immutable
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
     * Immutable
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
     * Immutable
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

    /**
     * Immutable
     *
     * @param Password $password
     *
     * @return BackupCollection
     */
    public function withPassword(Password $password): BackupCollection
    {
        $self = clone $this;
        $self->password = $password;

        return $self;
    }

    /**
     * Immutable
     *
     * @param Filename $filename
     *
     * @return BackupCollection
     */
    public function withFilename(Filename $filename): BackupCollection
    {
        $self = clone $this;
        $self->filename = $filename;

        return $self;
    }

    public function withUserGranted(User $token): BackupCollection
    {
        // don't allow to add same token twice
        foreach ($this->getAllowedTokens() as $existingToken) {
            if ($existingToken->getId() === $token->getId()) {
                return $this;
            }
        }

        $self = clone $this;
        $self->allowedTokens[] = $token;

        return $self;
    }

    public function withoutToken(User $tokenToRevokeAccessToCollection): BackupCollection
    {
        $self = clone $this;
        $self->allowedTokens = array_filter(
            $this->getAllowedTokens(),
            function (User $token) use ($tokenToRevokeAccessToCollection) {
                return !$token->isSameAs($tokenToRevokeAccessToCollection);
            }
        );

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
            'description'                 => $this->description,
            'filename'                    => $this->filename
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

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getFilename(): Filename
    {
        return $this->filename;
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

    /**
     * @return User[]
     */
    public function getAllowedTokens(): array
    {
        if (\is_object($this->allowedTokens)) {
            return $this->allowedTokens->toArray();
        }

        return $this->allowedTokens;
    }

    public function getMaxDiskSpaceCollectionCanAllocate(): DiskSpace
    {
        return DiskSpace::fromBytes(
            $this->getMaxOneVersionSize()->getValue() * $this->getMaxBackupsCount()->getValue()
        );
    }

    /**
     * @return BackupStrategy
     */
    public function getStrategy(): BackupStrategy
    {
        return $this->strategy;
    }
}
