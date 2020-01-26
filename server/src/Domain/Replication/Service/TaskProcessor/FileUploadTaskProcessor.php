<?php declare(strict_types=1);

namespace App\Domain\Replication\Service\TaskProcessor;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Exception\ReplicationProcessException;
use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;
use App\Domain\Replication\Repository\TokenRepository;
use App\Domain\Replication\Service\Client\RemoteFileProvider;
use App\Domain\SubmitDataTypes;

/**
 * Copies file from remote PRIMARY to local REPLICA
 * The metadata comes already fetched from the REPLICATION STREAM LIST
 */
class FileUploadTaskProcessor extends BaseTaskProcessor
{
    private RemoteFileProvider $fileProvider;

    public function __construct(DomainBus $domain, TokenRepository $tokenRepository,
                                RemoteFileProvider $fileProvider, ReplicationHistoryRepository $logRepository)
    {
        $this->fileProvider = $fileProvider;

        parent::__construct($domain, $tokenRepository, $logRepository);
    }

    public function processLog(ReplicationLogEntry $log): void
    {
        $form = $log->getForm();
        $form['stream'] = $this->readStream($log);
        $form['isFinalFilename'] = true;
        $form['fileOverwrite']   = true;

        try {
            $response = $this->domain->call(Bus::STORAGE_UPLOAD, [
                'form'  => $form,
                'token' => $this->tokenRepository->findApplicationInternalToken()
            ]);

        } catch (BusException $busException) {
            throw new ReplicationProcessException(
                'Unknown exception related to DomainBus, maybe a compatibility issue between PRIMARY and REPLICA?',
                null,
                $busException
            );
        }

        if (($response['status'] ?? '') !== 'OK') {
            throw new ReplicationProcessException(
                'Replication error: File cannot be uploaded, details: ' .
                \json_encode($response, JSON_THROW_ON_ERROR, 512)
            );
        }
    }

    /**
     * @param ReplicationLogEntry $log
     *
     * @return resource
     */
    public function readStream(ReplicationLogEntry $log)
    {
        return $this->fileProvider->fetch($log->getId());
    }

    public function canProcess(ReplicationLogEntry $log): bool
    {
        return $log->getType() === SubmitDataTypes::TYPE_FILE;
    }
}
