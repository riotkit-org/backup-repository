<?php declare(strict_types=1);

namespace App\Domain\Technical\ActionHandler;

use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Infrastructure\Common\Service\ORMConnectionCheck;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class HealthCheckHandler
{
    private FilesystemManager $fs;
    private ORMConnectionCheck $ormConnectionCheck;
    private string $secretCode;

    public function __construct(FilesystemManager $fs, ORMConnectionCheck $ORMConnectionCheck, string $secretCode)
    {
        $this->fs                 = $fs;
        $this->ormConnectionCheck = $ORMConnectionCheck;
        $this->secretCode         = $secretCode;
    }

    public function handle(string $code, bool $isCallingFromShell = false): array
    {
        if (($code !== $this->secretCode || !$code) && !$isCallingFromShell) {
            // @todo: Refactor into a domain exception
            throw new AccessDeniedHttpException();
        }

        $storageIsOk   = false;
        $dbIsOk        = false;

        $messages = [
            'database'        => [],
            'storage'         => []
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

        $globalStatus = $storageIsOk && $dbIsOk;

        return [
            'response' => [
                'status' => [
                    'storage'  => $storageIsOk,
                    'database' => $dbIsOk
                ],
                'messages'      => $messages,
                'global_status' => $globalStatus,
                'ident'         => [
                    'global_status=' . $this->boolToStr($globalStatus),
                    'storage=' . $this->boolToStr($storageIsOk),
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
