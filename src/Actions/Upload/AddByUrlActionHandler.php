<?php

namespace Actions\Upload;

use Actions\AbstractBaseAction;
use Exception\Upload\DuplicatedContentException;
use Manager\Domain\TagManagerInterface;
use Manager\FileRegistry;
use Service\HttpFileDownloader;
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
     * @var array $tags
     */
    private $tags = [];

    /**
     * @var TagManagerInterface $tagManager
     */
    private $tagManager;

    /**
     * @param string $fileUrl
     * @param array  $tags
     * @param TagManagerInterface $tagManager
     */
    public function __construct(string $fileUrl, array $tags, TagManagerInterface $tagManager)
    {
        $this->fileUrl    = $fileUrl;
        $this->tagManager = $tagManager;
        $this->tags       = $tags;
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

            /** @var HttpFileDownloader|Callable $downloader */
            $downloader = $this->getContainer()->offsetGet('service.http_file_downloader');
            $downloader = $downloader($this->fileUrl);
            $savedFile = $downloader->saveTo($targetPath);

            try {
                $file = $registry->registerByName($this->fileUrl, $savedFile->getFileMimeType());

                foreach ($this->tags as $tag) {
                    $this->tagManager->attachTagToFile($tag, $file);
                }

            } catch (DuplicatedContentException $e) {

                // on duplicate content redirect to other file
                $file = $e->getDuplicate(); // original file that WAS duplicated
                $registry->revertUploadedDuplicate($targetPath);

                foreach ($this->tags as $tag) {
                    $this->tagManager->attachTagToFile($tag, $file);
                }

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
