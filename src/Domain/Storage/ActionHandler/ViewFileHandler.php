<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Aggregate\BytesRangeAggregate;
use App\Domain\Storage\Context\CachingContext;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Response\FileDownloadResponse;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\Service\AlternativeFilenameResolver;
use App\Domain\Storage\ValueObject\Filename;
use GuzzleHttp\Psr7\Request;
use Ramsey\Http\Range\Range;
use Ramsey\Http\Range\UnitFactory;

class ViewFileHandler
{
    /**
     * @var StorageManager
     */
    private $storageManager;

    /**
     * @var FilesystemManager
     */
    private $fs;

    /**
     * @var AlternativeFilenameResolver
     */
    private $nameResolver;

    public function __construct(
        StorageManager $storageManager,
        FilesystemManager $fs,
        AlternativeFilenameResolver $nameResolver
    ) {
        $this->storageManager = $storageManager;
        $this->fs             = $fs;
        $this->nameResolver   = $nameResolver;
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
        $this->preProcessForm($form);

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

        return new FileDownloadResponse('OK', 200, function () use ($file, $form) {
            $out = fopen('php://output', 'wb');
            $res = $file->getStream()->attachTo();

            $allowLastModifiedHeader = true;
            $etagSuffix = '';
            $contentLength = $this->fs->getFileSize($file->getStoredFile()->getFilename());
            $acceptRange = 'bytes';

            //
            // Bytes range support (for streaming bigger files eg. video files)
            //
            $bytesRange = new BytesRangeAggregate($form->bytesRange, $contentLength);

            $maxLength = null;
            $offset = null;

            if ($bytesRange->shouldServePartialContent()) {
                $maxLength = $bytesRange->getTo()->getValue() - $bytesRange->getFrom()->getValue();
                $offset    = $bytesRange->getFrom()->getValue();
                $etagSuffix = $bytesRange->toHash();
                $acceptRange = $bytesRange->toBytesResponseString();
            }

            $this->sendHttpHeaders(
                $file->getStoredFile(),
                $etagSuffix,
                $allowLastModifiedHeader,
                $acceptRange,
                $contentLength
            );

            $maxLength && $offset ? stream_copy_to_stream($res, $out, $maxLength, $offset) : stream_copy_to_stream($res, $out);
            fclose($out);
            fclose($res);
        });
    }

    private function preProcessForm(ViewFileForm $form): void
    {
        $form->filename = $this->nameResolver->resolveFilename(new Filename($form->filename))->getValue();
    }

    private function sendHttpHeaders(StoredFile $file, string $eTagSuffix, bool $allowLastModifiedHeader, string $acceptRange, int $contentLength): void
    {
        if ($acceptRange) {
            header('Accept-Ranges: bytes');
        }

        if ($contentLength) {
            header('Content-Length: ' . $contentLength);
        }

        //
        // caching
        //
        if ($allowLastModifiedHeader) {
            header('Last-Modified:  ' . $file->getDateAdded()->format('D, d M Y H:i:s') . ' GMT');
        }

        header('ETag: ' . $file->getContentHash() . $eTagSuffix);
        header('Cache-Control: public, max-age=25200');

        //
        // others
        //
        header('Content-Type: ' . $file->getMimeType());
        header('Content-Disposition: attachment; filename="' . $file->getFilename()->getValue() . '"');
    }
}
