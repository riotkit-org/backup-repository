<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Response\FileDownloadResponse;
use App\Domain\Storage\ValueObject\Filename;

class ViewFileHandler
{
    /**
     * @var StorageManager
     */
    private $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    public function handle(string $filename): FileDownloadResponse
    {
        try {
            $file = $this->storageManager->retrieve(new Filename($filename));

        } catch (StorageException $exception) {

            if ($exception->getCode() === StorageException::codes['file_not_found']) {
                return new FileDownloadResponse($exception->getMessage(), 404);
            }

            return new FileDownloadResponse($exception->getMessage(), 500);
        }

        return new FileDownloadResponse('OK', 200, function () use ($file) {
            $out = fopen('php://output', 'wb');
            $res = $file->getStream()->attachTo();

            header('Content-Type: ' . $file->getStoredFile()->getMimeType());

            stream_copy_to_stream($res, $out);
            fclose($out);
            fclose($res);
        });
    }
}
