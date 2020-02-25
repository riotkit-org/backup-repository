<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\DTO\FileContent;

use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;

/**
 * @codeCoverageIgnore No logic, no test
 */
class StreamableFileContentWithEncryptionInformation extends StreamableFileContent
{
    private string $initializationVector;
    private EncryptionPassphrase $passphrase;
    private EncryptionAlgorithm $algorithm;

    public function __construct(
        string $fileName,
        callable $operationCallback,
        string $initializationVector,
        EncryptionPassphrase $passphrase,
        EncryptionAlgorithm $algorithm
    ) {
        $this->initializationVector = $initializationVector;
        $this->passphrase           = $passphrase;
        $this->algorithm            = $algorithm;

        parent::__construct($fileName, $operationCallback);
    }

    public function getInitializationVector(): string
    {
        return $this->initializationVector;
    }

    public function getPassphrase(): EncryptionPassphrase
    {
        return $this->passphrase;
    }

    public function getAlgorithm(): EncryptionAlgorithm
    {
        return $this->algorithm;
    }
}
