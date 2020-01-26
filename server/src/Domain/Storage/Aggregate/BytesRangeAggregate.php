<?php declare(strict_types=1);

namespace App\Domain\Storage\Aggregate;

use App\Domain\Common\ValueObject\Numeric\PositiveNumberOrZero;
use App\Domain\Storage\Exception\ContentRangeInvalidException;
use App\Domain\Storage\ValueObject\Filesize;

class BytesRangeAggregate
{
    private PositiveNumberOrZero $from;
    protected PositiveNumberOrZero $to;
    private Filesize $totalLength;
    private PositiveNumberOrZero $rangeContentLength;
    private PositiveNumberOrZero $fileSize;

    /**
     * @param string $headerValue
     * @param int $fileSize
     *
     * @throws ContentRangeInvalidException
     */
    public function __construct(string $headerValue, int $fileSize)
    {
        [$from, $to, $totalLength, $rangeLength] = $this->parse($headerValue, $fileSize);

        // http range header
        $this->from          = new PositiveNumberOrZero($from);
        $this->to            = new PositiveNumberOrZero($to);
        $this->totalLength   = new Filesize($totalLength);

        // content length
        $this->rangeContentLength = new PositiveNumberOrZero($rangeLength);

        // meta
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

    /**
     * @codeCoverageIgnore
     *
     * @return Filesize
     */
    public function getTotalLength(): Filesize
    {
        return $this->totalLength;
    }

    public function getRangeContentLength(): PositiveNumberOrZero
    {
        return $this->rangeContentLength;
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
        if (\strpos($headerValue, ',') !== false) {
            throw new ContentRangeInvalidException(0, $fileSize, $fileSize);
        }

        $actual = explode('=', $headerValue)[1] ?? '';
        $range  = explode('-', $actual);

        if ($headerValue && (!$actual || \count($range) === 0)) {
            throw new ContentRangeInvalidException(0, $fileSize, $fileSize);
        }

        $start = (int) $range[0] > 0 ? (int) $range[0] : 0;
        $end   = (int) ($range[1] ?? 0) > 0 ? (int) $range[1] : $fileSize;

        if ($end > $fileSize) {
            throw new ContentRangeInvalidException(0, $fileSize, $fileSize);
        }

        if ($end < $start) {
            throw new ContentRangeInvalidException(0, $fileSize, $fileSize);
        }

        return [
            // start
            $start,

            // end
            $end,

            // total length of the video
            $fileSize,

            // length of the range
            $end - $start
        ];
    }

    public function shouldServePartialContent(): bool
    {
        return $this->rangeContentLength->isLessThan($this->totalLength);
    }

    public function toHash(): string
    {
        return \hash(
            'md5',
            $this->getFrom()->getValue() . '_' .
            $this->getTo()->getValue() . '_' .
            $this->getRangeContentLength()->getValue()
        );
    }

    public function toBytesResponseString(): string
    {
        return 'bytes ' . $this->getFrom()->getValue() . '-' .
            $this->getTo()->getValue() . '/' .
            ($this->fileSize->getValue() + 1);
    }
}
