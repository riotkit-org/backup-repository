<?php declare(strict_types=1);

namespace App\Domain\Backup\Service;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Factory\NameFactory;
use App\Domain\Backup\Response\Internal\StorageUploadResponse;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Common\ValueObject\BaseUrl;

class FileUploader
{
    /**
     * @var DomainBus
     */
    private $bus;

    /**
     * @var NameFactory
     */
    private $nameFactory;

    public function __construct(DomainBus $bus, NameFactory $nameFactory)
    {
        $this->bus         = $bus;
        $this->nameFactory = $nameFactory;
    }

    public function upload(BackupCollection $collection, BaseUrl $baseUrl, Token $token): StorageUploadResponse
    {
        $responseAsArray = $this->bus->call(Bus::STORAGE_UPLOAD, [
            'form' => [
                'fileName'       => $this->nameFactory->getNextVersionName($collection)->getValue(),
                'fileOverwrite'  => false,
                'password'       => $collection->getPassword(),
                'tags'           => [],
                'backUrl'        => '',
                'public'         => false,
                'contentIdent'   => '_COLLECTION_' . $collection->getId()
            ],

            'baseUrl' => $baseUrl,
            'token'   => $token
        ]);

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
