<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity;

use App\Domain\Backup\Entity\Authentication\Token;
use App\Domain\Backup\ValueObject\BackupStrategy;
use App\Domain\Backup\ValueObject\Collection\BackupSize;
use App\Domain\Backup\ValueObject\Collection\CollectionLength;
use App\Domain\Backup\ValueObject\Collection\CollectionSize;

/**
 * Represents a single backup (eg. database dump from iwa-ait.org website) collection
 */
class BackupCollection
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var CollectionLength
     */
    private $maxBackupsCount;

    /**
     * @var BackupSize
     */
    private $maxOneVersionSize;

    /**
     * @var CollectionSize
     */
    private $maxCollectionSize;

    /**
     * @var Webhook[]
     */
    private $webhooks;

    /**
     * @var Token[]
     */
    private $allowedTokens;

    /**
     * Defines a strategy - should the older backups be deleted in favor of new backups, or should the user
     * delete backups manually and be alerted that the maximum is reached?
     *
     * @var BackupStrategy
     */
    private $strategy;

    /**
     * @var \DateTimeImmutable
     */
    private $creationDate;
}
