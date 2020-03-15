<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\DTO\FileContent;

use Psr\Http\Message\StreamInterface;

/**
 * @codeCoverageIgnore No logic, no test
 */
class StreamableFileContentWithEncryptionInformation extends StreamableFileContent
{
    private string $initializationVector;

    public function __construct(
        string $fileName,
        StreamInterface $operationCallback,
        string $initializationVector
    ) {
        $this->initializationVector = $initializationVector;

        parent::__construct($fileName, $operationCallback);
    }

    public function getInitializationVector(): string
    {
        return $this->initializationVector;
    }
}
