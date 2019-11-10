<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory;

use App\Domain\Common\Factory\UrlFactory;
use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Url;

class PublicUrlFactory
{
    /**
     * @var FileNameFactory
     */
    private $fileNameFactory;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    public function __construct(FileNameFactory $fileNameFactory, UrlFactory $urlFactory)
    {
        $this->fileNameFactory = $fileNameFactory;
        $this->urlFactory      = $urlFactory;
    }

    /**
     * Returns a new URL (public URL pointing to this service)
     *
     * @param Url $url Input file that was downloaded or is to be downloaded
     *
     * @return Url
     */
    public function fromExternalUrl(Url $url): Url
    {
        return new Url(
            $this->urlFactory->generate('storage.get_file', [
                'filename' => $this->fileNameFactory->fromUrl($url)->getValue()
            ])
        );
    }

    /**
     * Return a public URL pointing to a $file
     *
     * @param StoredFile $file
     * @param BaseUrl $baseUrl
     *
     * @return Url
     */
    public function fromStoredFile(StoredFile $file, BaseUrl $baseUrl): Url
    {
        return $this->fromFilename($file->getFilename(), $baseUrl);
    }

    /**
     * @param Filename $filename
     * @param BaseUrl $baseUrl
     *
     * @return Url
     */
    public function fromFilename(Filename $filename, BaseUrl $baseUrl): Url
    {
        return new Url(
            $this->urlFactory->generate('storage.get_file', [
                'filename' => $filename->getValue()
            ]),
            $baseUrl
        );
    }
}
