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
     * @param string $headerValue
     * @param int $fileSize
     *
     * @throws ContentRangeInvalidException
     */
    public function __construct(string $headerValue, int $fileSize)
    {
        [$from, $to, $length] = $this->parse($headerValue, $fileSize);

        $this->from   = new PositiveNumberOrZero($from);
        $this->to     = new PositiveNumberOrZero($to);
        $this->length = new Filesize($length);
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
        if (!$headerValue) {
            return [0, 0, 0];
        }

        $length     = $fileSize;
        $beginning  = 0;
        $fullEnding = $fileSize - 1;

        $streamEnd   = $fullEnding;

        [, $range] = explode('=', $headerValue, 2);

        if (\strpos($range, ',') !== false) {
            throw new ContentRangeInvalidException($beginning, $fullEnding, $length);
        }

        if ($range === '-') {
            $streamStart = $fileSize - substr($range, 1);

        } else {
            $range  = explode('-', $range);
            $streamStart = $range[0];
            $streamEnd   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $length;
        }

        $streamEnd = ($streamEnd > $fullEnding) ? $fullEnding : $streamEnd;

        if ($streamStart > $streamEnd || $streamStart > $length - 1 || $streamEnd >= $length) {
            throw new ContentRangeInvalidException($beginning, $fullEnding, $length);
        }

        return [
            (int) $streamStart,
            (int) $streamEnd,
            ($fullEnding - $beginning) + 1
        ];
    }
}
