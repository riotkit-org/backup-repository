<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Common\Http;
use App\Domain\Storage\Aggregate\BytesRangeAggregate;
use App\Domain\Storage\Aggregate\FileRetrievedFromStorage;
use App\Domain\Storage\Context\CachingContext;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Exception\ContentRangeInvalidException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Response\FileDownloadResponse;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\Service\AlternativeFilenameResolver;
use App\Domain\Storage\ValueObject\Filename;

/**
 * Response handler that serves file content.
 * Framework agnostic, acts like a controller
 *
 * Responsibilities:
 *   - Unpacking form arguments and passing to services
 *   - Handle errors and convert them to responses
 *   - Prepare HTTP headers for file serving, video streaming and caching
 */
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
                $this->sendHttpHeaders(
                    $file->getStoredFile(),
                    '',
                    true,
                    'bytes',
                    $this->fs->getFileSize($file->getStoredFile()->getFilename())
                );
            });
        }

        [$code, $streamHandler] = $this->createStreamHandler($file, $form);

        return new FileDownloadResponse('OK', $code, $streamHandler);
    }

    /**
     * @param FileRetrievedFromStorage $file
     * @param ViewFileForm $form
     *
     * @return array
     */
    private function createStreamHandler(FileRetrievedFromStorage $file, ViewFileForm $form): array
    {
        $out = fopen('php://output', 'wb');
        $res = $file->getStream()->attachTo();

        $allowLastModifiedHeader = true;
        $fileSize = $this->fs->getFileSize($file->getStoredFile()->getFilename());

        //
        // Bytes range support (for streaming bigger files eg. video files)
        //
        try {
            $bytesRange = new BytesRangeAggregate($form->bytesRange, $fileSize);

            $maxLength   = $bytesRange->getTotalLength()->getValue();
            $offset      = $bytesRange->getFrom()->getValue();
            $etagSuffix  = $bytesRange->toHash();
            $acceptRange = $bytesRange->toBytesResponseString();
            $contentLength = $bytesRange->getRangeContentLength()->getValue();

        } catch (ContentRangeInvalidException $rangeInvalidException) {
            return [Http::HTTP_INVALID_STREAM_RANGE, static function () use ($out, $res) {
                fclose($out);
                fclose($res);
            }];
        }

        $callback = function () use (
            $res, $out, $maxLength, $offset, $etagSuffix, $allowLastModifiedHeader, $file, $acceptRange, $contentLength
        ) {
            $this->sendHttpHeaders(
                $file->getStoredFile(),
                $etagSuffix,
                $allowLastModifiedHeader,
                $acceptRange,
                $contentLength
            );

            stream_copy_to_stream($res, $out, $maxLength, $offset);
            fclose($out);
            fclose($res);
        };

        return [$bytesRange->shouldServePartialContent() ? Http::HTTP_STREAM_PARTIAL_CONTENT : Http::HTTP_OK, $callback];
    }

    private function preProcessForm(ViewFileForm $form): void
    {
        $form->filename = $this->nameResolver->resolveFilename(new Filename($form->filename))->getValue();
    }

    private function sendHttpHeaders(StoredFile $file, string $eTagSuffix, bool $allowLastModifiedHeader, string $acceptRange, int $contentLength): void
    {
        if ($acceptRange) {
            header('Accept-Ranges: bytes');
            header('Content-Range: ' . $acceptRange);
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
