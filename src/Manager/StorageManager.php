<?php

namespace Manager;

use Model\Entity\File;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Exception\ImageManager\DirectoryNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Manages where to put a new file
 * and tells if putting a new file is possible
 * ===========================================
 *
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
     * @return string
     */
    public function getFileName(string $url)
    {
        $parts = explode('?', $url);
        return substr(md5($url), 0, 8) . '-' . pathinfo($parts[0], PATHINFO_BASENAME);
    }

    /**
     * @param string $url
     * @return string
     */
    public function getStorageFileName(string $url)
    {
       return $this->getFileName($url);
    }

    /**
     * @param string $url
     * @return string
     */
    public function getPathWhereToStoreTheFile(string $url)
    {
        return $this->storagePath . '/' . $this->getStorageFileName($url);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getUniquePathWhereToStorageFile(string $url)
    {
        $originalUrl = $url;

        while (is_file($this->getPathWhereToStoreTheFile($url))) {
            $url = rand(10000, 99999) . $originalUrl;
        }

        return $this->getPathWhereToStoreTheFile($url);
    }

    /**
     * Decide if we are able to write to selected path
     *
     * @param string $url
     * @return bool
     */
    public function canWriteFile($url)
    {
        return !is_file($this->getPathWhereToStoreTheFile($url));
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
     * @param File $file
     * @return string
     */
    public function getFileUrl(File $file)
    {
        return $this->router->generate('GET_public_download_imageName', [
            'imageName' => $this->getFileName($file->getFileName()),
        ]);
    }

    /**
     * @param string $url
     * @return string
     */
    public function getUrlByName($url)
    {
        if (substr($url, 0, 1) === '/' && is_file($url)) {
            $path = realpath($url);
            $path = explode($this->storagePath, $path);

            return $this->router->generate('GET_public_download_imageName', [
                'imageName' => ltrim($path[1], '/ '),
            ]);
        }

        return $this->router->generate('GET_public_download_imageName', [
            'imageName' => $this->getFileName($url),
        ]);
    }
}