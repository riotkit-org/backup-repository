<?php

namespace Manager;

use Silex\Application;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * @package Manager
 */
class ImageManager
{
    /** @var string $storagePath */
    private $storagePath;

    public function __construct(Application $app)
    {
        $this->storagePath = $app['storage.path'];
    }

    /**
     * @param string $url
     * @param bool   $withExtension
     *
     * @return string
     */
    public function getFileName($url, $withExtension = false)
    {
        $parts = explode('?', $url);

        return pathinfo($parts[0], ($withExtension ? PATHINFO_BASENAME : PATHINFO_FILENAME));
    }

    /**
     * @param string $url
     * @param bool   $withExtension
     *
     * @return string
     */
    public function getPathWhereToStoreTheImage($url, $withExtension = false)
    {
        return $this->storagePath . '/' . substr(md5($url), 0, 8) . '-' . $this->getFileName($url, $withExtension);
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function assertGetStoragePathForFile($fileName)
    {
        $fileName = str_replace('/', '', $fileName);
        $fileName = str_replace('..', '', $fileName);
        $fileName = str_replace("\x0", '', $fileName);
        $fileName = trim($fileName);
        $fileName = addslashes($fileName);

        if (!is_file($this->storagePath . '/' . $fileName)) {
            throw new FileNotFoundException('File not found');
        }

        return $this->storagePath . '/' . $fileName;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getImageUrl($url)
    {
        return '/repository/download/' . substr(md5($url), 0, 8) . '-' . $this->getFileName($url, true);
    }
}