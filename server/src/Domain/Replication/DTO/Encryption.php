<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO;

use App\Domain\Replication\ValueObject\EncryptionAlgorithm;
use App\Domain\Replication\ValueObject\EncryptionPassphrase;

class Encryption
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

    /**
     * @var callable $callback
     */
    private $callback;

    public function __construct(string $initializationVector, string $passphrase,
                                string $algorithm, callable $operationCallback)
    {
        $this->initializationVector = \bin2hex($initializationVector);
        $this->passphrase           = new EncryptionPassphrase($passphrase);
        $this->algorithm            = new EncryptionAlgorithm($algorithm);
        $this->callback             = $operationCallback;
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

    public function perform()
    {
        $callback = $this->callback;

        return $callback();
    }
}
