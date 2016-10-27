<?php

namespace Manager;

use Silex\Application;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Exception\ImageManager\DirectoryNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @package Manager
 */
class StorageManager
{
    /** @var string $storagePath */
    private $storagePath;

    /** @var UrlGenerator $router */
    private $router;

    public function __construct(string $storagePath, UrlGenerator $router)
    {
        $this->storagePath = realpath($storagePath);
        $this->router      = $router;

        if (!$this->storagePath) {
            throw new DirectoryNotFoundException('Storage path defined in "storage.path" configuration option does not exists');
        }
    }

    /**
     * @param string $url
     * @param bool   $withExtension
     *
     * @return string
     */
    public function getFileName(string $url, $withExtension = false)
    {
        $parts = explode('?', $url);
        return pathinfo($parts[0], ($withExtension ? PATHINFO_BASENAME : PATHINFO_FILENAME));
    }

    /**
     * @param string $url
     * @param bool   $withExtension
     * @param bool   $withPrefix
     * @return string
     */
    private function getStorageFileName(
        string $url,
        $withExtension = false,
        $withPrefix = true)
    {
        $fileName = ($withPrefix === true)
            ? substr(md5($url), 0, 8) . '-'
            : '';

        return $this->getFileName($url, $withExtension);
    }

    /**
     * @param string $url
     * @param bool   $withExtension
     * @param bool   $withPrefix
     *
     * @return string
     */
    public function getPathWhereToStoreTheFile(
        string $url,
        $withExtension = false,
        $withPrefix = true)
    {
        return $this->storagePath . '/' . $this->getStorageFileName($url, $withExtension, $withPrefix);
    }

    /**
     * @param string $url
     * @param bool   $withExtension
     * @param bool   $withPrefix
     *
     * @return string
     */
    public function getUniquePathWhereToStorageFile(
        string $url,
        $withExtension = false,
        $withPrefix    = true)
    {
        $originalUrl = $url;

        while (is_file($this->getPathWhereToStoreTheFile($url, $withExtension, $withPrefix))) {
            $url = rand(10000, 99999) . $originalUrl;
        }

        return $this->getPathWhereToStoreTheFile($url, $withExtension, $withPrefix);
    }

    /**
     * Decide if we are able to write to selected path
     *
     * @param string $url
     * @param bool   $withExtension
     * @return bool
     */
    public function canWriteFile($url, $withExtension = false)
    {
        return !is_file($this->getPathWhereToStoreTheFile($url, $withExtension));
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
    public function getFileUrl($url)
    {
        if (substr($url, 0, 1) === '/' && is_file($url)) {
            $path = realpath($url);
            $path = explode($this->storagePath, $path)[0];

            return $this->router->generate('GET_public_download_imageName', [
                'imageName' => $path[1],
            ]);
        }

        return $this->router->generate('GET_public_download_imageName', [
            'imageName' => $this->getFileName($url, true),
        ]);
    }
}