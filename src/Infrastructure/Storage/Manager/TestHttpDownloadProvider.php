<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Manager;

use App\Domain\Storage\Provider\HttpDownloadProvider;
use App\Domain\Storage\ValueObject\Stream;
use App\Domain\Storage\ValueObject\Url;

class TestHttpDownloadProvider implements HttpDownloadProvider
{
    /**
     * @var HttpDownloadProvider
     */
    private $parentProvider;

    public function __construct(HttpDownloadProvider $parentProvider)
    {
        $this->parentProvider = $parentProvider;
    }

    public function getStreamFromUrl(Url $url): Stream
    {
        if ($url->isLocalFileUrl()) {
            return new Stream(fopen($url->getValue(), 'rb'));
        }

        return $this->parentProvider->getStreamFromUrl($url);
    }
}
