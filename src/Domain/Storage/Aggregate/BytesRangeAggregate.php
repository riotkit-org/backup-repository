<?php declare(strict_types=1);

namespace App\Domain\Storage\Aggregate;

use App\Domain\Common\ValueObject\Numeric\PositiveNumberOrZero;
use App\Domain\Storage\Exception\ContentRangeInvalidException;
use App\Domain\Storage\ValueObject\Filesize;

class BytesRangeAggregate
{
    /**
     * @var PositiveNumberOrZero $from
     */
    private $from;

    /**
     * @var PositiveNumberOrZero $to
     */
    private $to;

    /**
     * @var Filesize
     */
    private $length;

    /**
     * @var PositiveNumberOrZero
     */
    private $contentLength;

    /**
     * @var PositiveNumberOrZero
     */
    private $fileSize;

    /**
     * @param string $headerValue
     * @param int $fileSize
     *
     * @throws ContentRangeInvalidException
     */
    public function __construct(string $headerValue, int $fileSize)
    {
        [$from, $to, $length, $contentLength] = $this->parse($headerValue, $fileSize);

        $this->from          = new PositiveNumberOrZero($from);
        $this->to            = new PositiveNumberOrZero($to);
        $this->length        = new Filesize($length);
        $this->contentLength = new PositiveNumberOrZero($contentLength);
        $this->fileSize      = new PositiveNumberOrZero($fileSize);
    }

    public function getFrom(): PositiveNumberOrZero
    {
        return $this->from;
    }

    public function getTo(): PositiveNumberOrZero
    {
        return $this->to;
    }

    public function getLength(): Filesize
    {
        return $this->length;
    }

    public function getContentLength(): PositiveNumberOrZero
    {
        return $this->contentLength;
    }

    /**
     * @param string $headerValue
     * @param int $fileSize
     *
     * @return array
     *
     * @throws ContentRangeInvalidException
     */
    private function parse(string $headerValue, int $fileSize): array
    {
        $length     = $fileSize + 1;
        $beginning  = 0;
        $ending     = $fileSize;

        if (!$headerValue) {
            return [
                $beginning,
                $ending,
                $length,
                $ending - $beginning
            ];
        }

        [, $range] = explode('=', $headerValue, 2);

        if (\strpos($range, ',') !== false) {
            throw new ContentRangeInvalidException($beginning, $ending, $length);
        }

        [$start, $end] = explode('-', $range);
        $start = (int) $start;
        $end   = (int) $end;

        if ($start < $end && $start > 0 && $start < $fileSize) {
            $beginning = $start;
        }

        if ($end <= $fileSize && $end > 0 && $end >= $start) {
            $ending = $end;
        }

        return [
            $beginning,
            $ending,
            $length,
            $ending - $beginning
        ];
    }

    public function shouldServePartialContent(): bool
    {
        return $this->getFrom()->isHigherThanInteger(0)
            && !$this->getTo()->isSameAs($this->fileSize);
    }

    public function toHash(): string
    {
        return \hash(
            'md5',
            $this->getFrom()->getValue() . '_' .
            $this->getTo()->getValue() . '_' .
            $this->getLength()->getValue()
        );
    }

    public function toBytesResponseString(): string
    {
        return 'bytes ' . $this->getFrom()->getValue() . '-' .
            $this->getTo()->getValue() . '/' .
            $this->getLength()->getValue();
    }
}
