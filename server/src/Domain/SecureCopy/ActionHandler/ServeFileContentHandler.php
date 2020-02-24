<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ActionHandler;

use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Exception\ValidationException;
use App\Domain\SecureCopy\Repository\CryptoMapRepository;
use App\Domain\SecureCopy\Response\FileReadingResponse;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SecureCopy\Service\FileReadService;
use App\Domain\SubmitDataTypes;

/**
 * Serves file content with encryption on-the-fly support
 */
class ServeFileContentHandler extends BaseSecureCopyHandler
{
    private FileReadService $frs;

    public function __construct(FileReadService $frs, CryptoMapRepository $repository)
    {
        $this->frs                 = $frs;
        $this->idMappingRepository = $repository;
    }

    /**
     * @param string   $encryptedId
     * @param resource $output
     *
     * @param MirroringContext $context
     *
     * @return FileReadingResponse
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function handle(string $encryptedId, $output, MirroringContext $context): FileReadingResponse
    {
        $this->assertHasRights($context);

        $fileId = $this->decryptIdIfNecessary(SubmitDataTypes::TYPE_FILE, $encryptedId, $context);

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
