<?php declare(strict_types=1);

namespace App\Domain\Backup\Service;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Factory\NameFactory;
use App\Domain\Backup\Response\Internal\StorageUploadResponse;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Backup\ValueObject\JWT;
use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;

class FileUploader
{
    /**
     * @var DomainBus
     */
    private DomainBus $bus;

    /**
     * @var NameFactory
     */
    private NameFactory $nameFactory;

    public function __construct(DomainBus $bus, NameFactory $nameFactory)
    {
        $this->bus         = $bus;
        $this->nameFactory = $nameFactory;
    }

    /**
     * @param BackupCollection $collection
     * @param User $user
     * @param JWT $accessToken
     *
     * @return StorageUploadResponse
     *
     * @throws AuthenticationException
     * @throws BusException
     */
    public function upload(BackupCollection $collection, User $user, JWT $accessToken): StorageUploadResponse
    {
        $responseAsArray = $this->bus->call(Bus::STORAGE_UPLOAD, [
            'form' => [
                'fileName'       => $this->nameFactory->getNextVersionName($collection)->getValue(),
                'fileOverwrite'  => false,
                'password'       => $collection->getPassword(),
                'tags'           => [],
                'public'         => false
            ],

            'user'        => $user,
            'accessToken' => $accessToken
        ]);

        if ($responseAsArray['status'] > 299) {
            throw AuthenticationException::fromBackupUploadActionDisallowed();
        }

        return StorageUploadResponse::createFromArray($responseAsArray);
    }

    public function rollback(?StorageUploadResponse $response): void
    {
        if (!$response || !$response->isSuccess()) {
            return;
        }

        $this->bus->call(Bus::STORAGE_DELETE, [
            'form' => [
                'filename' => $response->getFilename()
            ]
        ]);
    }

    public function deletePreviouslyUploaded(Filename $getFilename): bool
    {
        return $this->bus->call(Bus::STORAGE_DELETE, [
            'form' => [
                'filename' => $getFilename->getValue()
            ]
        ]);
    }
}
