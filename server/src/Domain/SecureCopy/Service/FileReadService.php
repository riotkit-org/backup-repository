<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Service;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\SecureCopy\DTO\FileContent\StreamableFileContent;
use App\Domain\SecureCopy\DTO\FileContent\StreamableFileContentWithEncryptionInformation;
use App\Domain\SecureCopy\Entity\Authentication\Token;
use App\Domain\SecureCopy\Exception\StorageReadError;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;
use GuzzleHttp\Psr7\Stream;

/**
 * Reads a file from local File Repository instance
 * Supports encryption for zero-knowledge SecureCopy
 *
 * The implementation was made using a shell command because in PHP it is very difficult (if not impossible)
 * to correctly encrypt on-the-fly a bigger file.
 *
 * The encryption using openssl shell command makes decryption a lot easier for potential data recovery.
 */
class FileReadService
{
    private DomainBus     $domain;
    private CryptoService $cryptoService;

    public function __construct(DomainBus $bus, CryptoService $cryptoService)
    {
        $this->domain = $bus;
        $this->cryptoService = $cryptoService;
    }

    /**
     * @param string $filename
     * @param MirroringContext $context
     *
     * @return StreamableFileContentWithEncryptionInformation
     *
     * @throws BusException
     * @throws StorageReadError
     */
    public function getEncryptedStream(string $filename, MirroringContext $context): StreamableFileContentWithEncryptionInformation
    {
        $out = $this->getFromStorage($filename, $context->getToken());
        $cryptoStream = $this->cryptoService->encode($out['stream'], $context->getCryptographySpecification());

        return new StreamableFileContentWithEncryptionInformation(
            $filename,
            $cryptoStream->getStream(),
            $cryptoStream->getIv()
        );
    }

    /**
     * @param string $filename
     * @param MirroringContext $context
     *
     * @return StreamableFileContent
     *
     * @throws BusException
     * @throws StorageReadError
     */
    public function getPlainStream(string $filename, MirroringContext $context)
    {
        $storageOut = $this->getFromStorage($filename, $context->getToken());

        return new StreamableFileContent(
            $filename,
            new Stream($storageOut['stream'])
        );
    }

    /**
     * @param string $filename
     * @param Token $token
     *
     * @return array
     *
     * @throws BusException
     * @throws StorageReadError
     */
    private function getFromStorage(string $filename, Token $token): array
    {
        $out = $this->domain->call(Bus::STORAGE_VIEW_FILE, [
            'isFileAlreadyValidated' => true,
            'token'                  => $token->getId(),
            'filename'               => $filename,
            'password'               => '',
            'bytesRange'             => '',
            'ifNoneMatch'            => '',
            'ifModifiedSince'        => ''
        ]);

        if ($out['response']['code'] === 404) {
            throw StorageReadError::createStorageNotFoundException();
        }

        if ($out['response']['code'] > 299) {
            throw StorageReadError::createStorageUnknownError($out['response']['status']);
        }

        return $out;
    }

    public function generateShellCryptoCommand(
        EncryptionAlgorithm $algorithm,
        EncryptionPassphrase $password,
        string $iv
    ): string {

        $ivStr = $iv ? ' -iv "%iv%" ' : ' '; // not all algorithms requires IV
        $template = 'openssl enc -%algorithm_name% -salt -iter 6000 -K "%passphrase%" ' . $ivStr . ' ';

        return str_replace(
            ['%algorithm_name%', '%passphrase%', '%iv%'],
            [$algorithm->getValue(), $password->getAsHex(), $iv],
            $template
        );
    }
}
