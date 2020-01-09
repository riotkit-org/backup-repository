<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Response\FileReadingResponse;
use App\Domain\Replication\Security\ReplicationContext;
use App\Domain\Replication\Service\FileReadService;

/**
 * Serves file content with encryption on-the-fly support
 */
class ServeFileContentHandler extends BaseReplicationHandler
{
    /**
     * @var FileReadService
     */
    private $frs;

    public function __construct(FileReadService $frs)
    {
        $this->frs = $frs;
    }

    /**
     * @param string $fileId
     * @param resource $output
     *
     * @return FileReadingResponse
     * @throws AuthenticationException
     */
    public function handle(string $fileId, $output, ReplicationContext $context): FileReadingResponse
    {
        $this->assertHasRights($context);

        if ($context->isEncryptionActive()) {
            return $this->handleEncrypted($fileId, $output, $context);
        }

        return $this->handlePlain($fileId, $output);
    }

    private function handlePlain(string $fileId, $output)
    {
        return FileReadingResponse::createOkResponseForStream(
            $this->frs->getPlainStream($fileId, $output)
        );
    }

    private function handleEncrypted(string $fileId, $output, ReplicationContext $context)
    {
        return FileReadingResponse::createOkResponseForStream(
            $this->frs->getEncryptedStream($fileId, $output, $context)
        );
    }
}
