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

    public function __construct(int $from, int $to, int $length, Throwable $previous = null)
    {
        $this->from   = $from;
        $this->to     = $to;
        $this->length = $length;

        parent::__construct(
            Errors::ERR_MSG_STORAGE_READ_CONTENT_RANGE_INVALID,
            Errors::ERR_STORAGE_READ_CONTENT_RANGE_INVALID,
            $previous
        );
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
