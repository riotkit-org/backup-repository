<?php declare(strict_types=1);

namespace App\Domain\Storage\Provider;

use App\Domain\Storage\ValueObject\Stream;
use App\Domain\Storage\ValueObject\Url;

interface HttpDownloadProvider
{
    /**
     * Turn HTTP/HTTPS resource into the stream
     * so we can copy it later
     *
     * @param Url $url
     *
     * @return Stream
     */
    public function getStreamFromUrl(Url $url): Stream;
}
