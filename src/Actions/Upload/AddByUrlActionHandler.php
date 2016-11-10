<?php

namespace Actions\Upload;

use Actions\AbstractBaseAction;
use Exception\Upload\DuplicatedContentException;
use Manager\FileRegistry;
use Service\HttpImageDownloader;
use Manager\StorageManager;
use Exception\ImageManager\InvalidUrlException;

/**
 * @package Actions\Upload
 */
class AddByUrlActionHandler extends AbstractBaseAction
{
    /**
     * @var string $fileUrl
     */
    private $fileUrl;

    /**
     * @param string $fileUrl
     */
    public function __construct(string $fileUrl)
    {
        $this->fileUrl = $fileUrl;
    }

    /**
     * @throws InvalidUrlException
     * @return array
     */
    public function execute(): array
    {
        $this->assertValidUrl($this->fileUrl);

        /**
         * @var StorageManager $manager
         * @var FileRegistry   $registry
         */
        $manager    = $this->getContainer()->offsetGet('manager.storage');
        $registry   = $this->getContainer()->offsetGet('manager.file_registry');

        if (!$registry->existsInRegistry($this->fileUrl)) {
            $targetPath = $manager->getPathWhereToStoreTheFile($this->fileUrl);

            $downloader = new HttpImageDownloader($this->fileUrl);
            $savedFile = $downloader->saveTo($targetPath);

            try {
                $file = $registry->registerByName($this->fileUrl, $savedFile->getFileMimeType());

            } catch (DuplicatedContentException $e) {

                // on duplicate content redirect to other file
                $file = $e->getDuplicate(); // original file that WAS duplicated
                $registry->revertUploadedDuplicate($targetPath);

                return [
                    'status' => 'OK',
                    'code'   => 301,
                    'url'    => $manager->getFileUrl($file),
                ];
            }

            return [
                'status' => 'OK',
                'code'   => 200,
                'url'    => $manager->getFileUrl($file),
            ];
        }

        return [
            'status' => 'Not-Changed',
            'code'   => 200,
            'url'    => $manager->getUrlByName($this->fileUrl),
        ];
    }

    /**
     * @param string $url
     *
     * @throws InvalidUrlException
     * @return bool
     */
    public function assertValidUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException();
        }

        if (!$this->getController()->supportsProtocol(parse_url($url, PHP_URL_SCHEME))) {
            throw new InvalidUrlException(InvalidUrlException::INVALID_SCHEMA);
        }
    }
}