<?php declare(strict_types=1);

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

    public function __construct(
        string $storagePath,
        UrlGenerator $router,
        string $webUrl
    ) {
        $this->storagePath = realpath($storagePath);
        $this->router      = $router;
        $this->weburl      = $webUrl;

        if (!$this->storagePath) {
            throw new DirectoryNotFoundException('Storage path defined in "storage.path" configuration option does not exists');
        }
    }

    /**
     * Escape path, make sure it will not go out of the storagePath
     *
     * @param string $path
     * @return string
     */
    public function escapeTargetPath(string $path): string
    {
        $fileName = pathinfo($path, PATHINFO_BASENAME);
        $directory = realpath(dirname($path));

        if ($directory !== realpath($this->storagePath)) {
            return '';
        }

        return $directory . '/' . $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function escapeName(string $fileName): string
    {
        $fileName = str_replace('..', '', $fileName);
        $fileName = str_replace('/', '-', $fileName);

        return $fileName;
    }

    /**
     * @param string $url
     * @param bool   $withPrefix
     *
     * @return string
     */
    public function getFileName(string $url, $withPrefix = true)
    {
        $parts = explode('?', $url);
        $name  = '';

        if ($withPrefix == true) {
            $name .= substr(md5($url), 0, 8) . '-';
        }

        return $name . pathinfo($parts[0], PATHINFO_BASENAME);
    }

    /**
     * @param string $url
     * @param bool   $withPrefix
     *
     * @return string
     */
    public function getStorageFileName(string $url, $withPrefix = true)
    {
       if (is_file($this->storagePath . '/' . $url)) {
           return $url;
       }

       return $this->getFileName($url, $withPrefix);
    }

    /**
     * @param string $url
     * @param bool   $withPrefix
     *
     * @return string
     */
    public function getPathWhereToStoreTheFile(string $url, $withPrefix = true)
    {
        return $this->escapeTargetPath(
            $this->storagePath . '/' . $this->getStorageFileName($url, $withPrefix)
        );
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
        $path = $this->getPathWhereToStoreTheFile($url);

        return !is_file($path) && is_writable(dirname($path));
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function assertGetStoragePathForFile(string $fileName)
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
    public function getFileUrl(File $file): string
    {
        return $this->weburl . $this->router->generate('GET_public_download_imageName', [
            'imageName' => $file->getFileName(),
        ]);
    }

    /**
     * @param string $url
     * @return string
     */
    public function getUrlByName(string $url): string
    {
        if (substr($url, 0, 1) === '/' && is_file($url)) {
            $path = realpath($url);
            $path = explode($this->storagePath, $path);

            return $this->weburl . $this->router->generate('GET_public_download_imageName', [
                'imageName' => ltrim($path[1], '/ '),
            ]);
        }

        return $this->weburl . $this->router->generate('GET_public_download_imageName', [
            'imageName' => $this->getFileName($url),
        ]);
    }

    /**
     * @return string
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }
}