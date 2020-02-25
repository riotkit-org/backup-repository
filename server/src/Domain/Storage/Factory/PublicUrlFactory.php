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
     * @var UrlFactory
     */
    private UrlFactory $urlFactory;

    private BaseUrl $baseUrl;

    public function __construct(UrlFactory $urlFactory, string $baseUrl)
    {
        $this->urlFactory = $urlFactory;
        $this->baseUrl    = new BaseUrl($baseUrl);
    }

    /**
     * Return a public URL pointing to a $file
     *
     * @param StoredFile $file
     *
     * @return Url
     */
    public function fromStoredFile(StoredFile $file): Url
    {
        return $this->fromFilename($file->getFilename());
    }

    public function fromFilename(Filename $filename): Url
    {
        return new Url(
            $this->urlFactory->generate('storage.get_file', [
                'filename' => $filename->getValue()
            ]),
            $this->baseUrl
        );
    }
}
