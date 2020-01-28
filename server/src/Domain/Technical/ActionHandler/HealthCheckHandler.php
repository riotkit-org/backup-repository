<?php declare(strict_types=1);

namespace App\Domain\Technical\ActionHandler;

use App\Domain\Replication\Exception\ReplicaNodeUnhealthyError;
use App\Domain\Replication\Service\ReplicationStatusCheck;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Infrastructure\Common\Service\ORMConnectionCheck;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class HealthCheckHandler
{
    private FilesystemManager $fs;
    private ORMConnectionCheck $ormConnectionCheck;
    private ReplicationStatusCheck $replicationCheck;
    private string $secretCode;

    public function __construct(FilesystemManager $fs,
                                ORMConnectionCheck $ORMConnectionCheck,
                                ReplicationStatusCheck $replicationCheck,
                                string $secretCode)
    {
        $this->fs                 = $fs;
        $this->ormConnectionCheck = $ORMConnectionCheck;
        $this->replicationCheck   = $replicationCheck;
        $this->secretCode         = $secretCode;
    }

    public function handle(string $code): array
    {
        if ($code !== $this->secretCode || !$code) {
            // @todo: Refactor into a domain exception
            throw new AccessDeniedHttpException();
        }

        $storageIsOk   = false;
        $dbIsOk        = false;
        $replicaStatus = false;

        $messages = [
            'database'        => [],
            'storage'         => [],
            'replica_status'  => []
        ];

        // database
        try {
            $dbIsOk = $this->ormConnectionCheck->test();

        } catch (\Exception $exception) {
            $messages['database'][] = $exception->getMessage();
        }

        // filesystem
        try {
            $this->fs->test();
            $storageIsOk = true;

        } catch (StorageException $exception) {
            $messages['storage'][] = $exception->getMessage();

            if ($exception->getPrevious()) {
                $messages['storage'][] = $exception->getPrevious()->getMessage();
            }
        }

        // replication
        try {
            $this->replicationCheck->assertIsHealthy();
            $replicaStatus = $this->replicationCheck->isConfiguredAsReplica() ? true : 'Not configured as replica';

        } catch (ReplicaNodeUnhealthyError $exception) {
            $messages['replica_status'][] = $exception->getMessage();
            $replicaStatus = false;
        }

        $globalStatus = $storageIsOk && $dbIsOk && $replicaStatus !== false;

        return [
            'response' => [
                'status' => [
                    'storage'  => $storageIsOk,
                    'database' => $dbIsOk,
                    'replica'  => $replicaStatus
                ],
                'messages'      => $messages,
                'global_status' => $globalStatus,
                'ident'         => [
                    'global_status=' . $this->boolToStr($globalStatus),
                    'storage=' . $this->boolToStr($storageIsOk),
                    'replica=' . $this->boolToStr($replicaStatus),
                    'database=' . $this->boolToStr($dbIsOk)
                ]
            ],
            'status' => $globalStatus
        ];
    }

    private function boolToStr(bool $value): string
    {
        return $value ? 'True' : 'False';
    }

    public function getSecretCode(): string
    {
        return $this->secretCode;
    }
}
