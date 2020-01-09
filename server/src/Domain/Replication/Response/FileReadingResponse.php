<?php declare(strict_types=1);

namespace App\Domain\Replication\Response;

use App\Domain\Replication\DTO\FileContent\StreamableFileContent;
use App\Domain\Replication\DTO\FileContent\StreamableFileContentWithEncryptionInformation;

class FileReadingResponse
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var callable
     */
    private $callback;

    public static function createOkResponseForStream(StreamableFileContent $encryption): FileReadingResponse
    {
        $new = new static();
        $new->status      = 'OK';
        $new->statusCode  = 200;
        $new->callback    = $encryption->getStreamFlushingCallback();
        $new->headers     = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $encryption->getFileName() . '"'
        ];

        if ($encryption instanceof StreamableFileContentWithEncryptionInformation) {
            $new->headers['Encryption-Initialization-Vector'] = $encryption->getInitializationVector();
        }

        return $new;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getFlushingCallback(): callable
    {
        return $this->callback;
    }
}
