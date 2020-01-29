<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ActionHandler;

use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Response\FileReadingResponse;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SecureCopy\Service\FileReadService;

/**
 * Serves file content with encryption on-the-fly support
 */
class ServeFileContentHandler extends BaseSecureCopyHandler
{
    private FileReadService $frs;

    public function __construct(FileReadService $frs)
    {
        $this->frs = $frs;
    }

    /**
     * @param string $fileId
     * @param resource $output
     *
     * @param MirroringContext $context
     *
     * @return FileReadingResponse
     * @throws AuthenticationException
     */
    public function handle(string $fileId, $output, MirroringContext $context): FileReadingResponse
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

    private function handleEncrypted(string $fileId, $output, MirroringContext $context)
    {
        return FileReadingResponse::createOkResponseForStream(
            $this->frs->getEncryptedStream($fileId, $output, $context)
        );
    }
}
