<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Errors;
use Throwable;

class ContentRangeInvalidException extends CommonValueException
{
    /**
     * @var int
     */
    private int $from;

    /**
     * @var int
     */
    private int $to;

    /**
     * @var int
     */
    private int $length;

    public static function createFromRange(int $from, int $to, int $length, Throwable $previous = null)
    {
        $exc = new static(
            Errors::ERR_MSG_STORAGE_READ_CONTENT_RANGE_INVALID,
            Errors::ERR_STORAGE_READ_CONTENT_RANGE_INVALID,
            $previous
        );

        $exc->from   = $from;
        $exc->to     = $to;
        $exc->length = $length;

        return $exc;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getTo(): int
    {
        return $this->to;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
