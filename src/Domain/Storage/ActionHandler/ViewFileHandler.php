<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Context\CachingContext;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Response\FileDownloadResponse;
use App\Domain\Storage\Security\ReadSecurityContext;
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

    /**
     * @param ViewFileForm        $form
     * @param ReadSecurityContext $securityContext
     * @param CachingContext      $cachingContext
     *
     * @return FileDownloadResponse
     *
     * @throws AuthenticationException
     */
    public function handle(ViewFileForm $form, ReadSecurityContext $securityContext, CachingContext $cachingContext): FileDownloadResponse
    {
        try {
            $file = $this->storageManager->retrieve(new Filename((string) $form->filename));

        } catch (StorageException $exception) {

            if ($exception->getCode() === StorageException::codes['file_not_found']) {
                return new FileDownloadResponse($exception->getMessage(), 404);
            }

            return new FileDownloadResponse($exception->getMessage(), 500);
        }

        if (!$securityContext->isAbleToViewFile($file->getStoredFile())) {
            throw new AuthenticationException(
                'No access to read the file, maybe invalid password?',
                AuthenticationException::CODES['no_read_access_or_invalid_password']
            );
        }

        if (!$cachingContext->isCacheExpiredForFile($file->getStoredFile())) {
            return new FileDownloadResponse('Not Modified', 304, function () use ($file) {
                $this->sendHttpHeaders($file->getStoredFile());
            });
        }

        return new FileDownloadResponse('OK', 200, function () use ($file) {
            $out = fopen('php://output', 'wb');
            $res = $file->getStream()->attachTo();

            $this->sendHttpHeaders($file->getStoredFile());

            stream_copy_to_stream($res, $out);
            fclose($out);
            fclose($res);
        });
    }

    private function sendHttpHeaders(StoredFile $file): void
    {
        header('Content-Type: ' . $file->getMimeType());
        header('Last-Modified:  ' . $file->getDateAdded()->format('D, d M Y H:i:s') . ' GMT');
        header('ETag: ' . $file->getContentHash());
        header('Cache-Control: public, max-age=25200');
    }
}
