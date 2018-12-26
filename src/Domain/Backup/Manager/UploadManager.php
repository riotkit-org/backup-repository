<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Factory\NameFactory;
use App\Domain\Backup\Response\StorageUploadResponse;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Common\ValueObject\BaseUrl;

class UploadManager
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
                'fileName'      => $this->nameFactory->getNextVersionName($collection)->getValue(),
                'fileOverwrite' => false,
                'password'      => $collection->getPassword(),
                'tags'          => [],
                'backUrl'       => '',
                'public'        => false
            ],

            'baseUrl' => $baseUrl,
            'token'   => $token
        ]);

        return StorageUploadResponse::createFromArray($responseAsArray);
    }

    public function rollback(?StorageUploadResponse $response)
    {
        if (!$response) {
            return;
        }

        $this->bus->call(Bus::STORAGE_DELETE, [
            'form' => [
                'filename' => $response->getFilename()
            ]
        ]);
    }
}
