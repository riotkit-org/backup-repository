<?php declare(strict_types=1);

namespace App\Domain\Storage\Provider;

use App\Domain\Storage\ValueObject\Stream;

class UserUploadProvider
{
    /**
     * @var array
     */
    private $requestHeaders;

    public function __construct()
    {
        $this->requestHeaders = $_SERVER;
    }

    /**
     * Read user input and create a stream from it
     *
     * @return Stream
     */
    public function getStreamFromHttp(): Stream
    {
        if ($this->hasPostedViaFormUrlEncoded()) {
            $filesIndexes = array_keys($_FILES);
            $file = $_FILES[$filesIndexes[0]];

            return new Stream(fopen($file['tmp_name'], 'rb'));
        }

        if ($this->hasPostedRaw()) {
            return new Stream(fopen('php://input', 'rb'));
        }

        throw new \LogicException('User not provided any valid source of file with the HTTP protocol');
    }

    private function hasPostedViaFormUrlEncoded(): bool
    {
        // could check $this->hasUserSentUrlEncodedContentType(), but will not, we can be more fault-tolerant
        return \count($_FILES) > 0;
    }

    private function hasPostedRaw(): bool
    {
        if ($this->hasUserSentUrlEncodedContentType()) {
            return false;
        }

        $input = fopen('php://input', 'rb');
        $part = fread($input, 512);
        fclose($input);

        return \strlen($part) > 8;
    }

    private function hasUserSentUrlEncodedContentType(): bool
    {
        $headerValue = ($this->requestHeaders['CONTENT_TYPE'] ?? '');

        return \strtolower(\trim($headerValue)) === 'application/x-www-form-urlencoded';
    }
}
