<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO\FileContent;

use App\Domain\Replication\ValueObject\EncryptionAlgorithm;
use App\Domain\Replication\ValueObject\EncryptionPassphrase;

class StreamableFileContentWithEncryptionInformation extends StreamableFile
{
    /**
     * @var string
     */
    private $initializationVector;

    /**
     * @var EncryptionPassphrase
     */
    private $passphrase;

    /**
     * @var EncryptionAlgorithm
     */
    private $algorithm;

    public function __construct(
        callable $operationCallback,
        string $initializationVector,
        EncryptionPassphrase $passphrase,
        EncryptionAlgorithm $algorithm
    ) {
        $this->initializationVector = $initializationVector;
        $this->passphrase           = $passphrase;
        $this->algorithm            = $algorithm;

        parent::__construct($operationCallback);
    }

    /**
     * @return string
     */
    public function getInitializationVector(): string
    {
        return $this->initializationVector;
    }

    /**
     * @return EncryptionPassphrase
     */
    public function getPassphrase(): EncryptionPassphrase
    {
        return $this->passphrase;
    }

    /**
     * @return EncryptionAlgorithm
     */
    public function getAlgorithm(): EncryptionAlgorithm
    {
        return $this->algorithm;
    }
}
