<?php

namespace Actions\Upload;

use Actions\AbstractBaseAction;
use Service\HttpImageDownloader;
use Manager\ImageManager;
use Exception\ImageManager\InvalidUrlException;

/**
 * @package Actions\Upload
 */
class UploadActionHandler extends AbstractBaseAction
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

        /** @var ImageManager $manager */
        $manager    = $this->getContainer()->offsetGet('manager.image');
        $targetPath = $manager->getPathWhereToStoreTheImage($this->fileUrl, true);
        $modified   = false;

        if (!is_file($targetPath)) {
            $downloader = new HttpImageDownloader($this->fileUrl);
            $downloader->saveTo(
                $targetPath, false
            );

            $modified = true;
        }

        return [
            'status' => $modified ? 'OK' : 'Not-Changed',
            'code'   => 200,
            'url'    => $manager->getImageUrl($this->fileUrl),
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