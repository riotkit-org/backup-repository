<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

use App\Domain\Common\Exception\ValueObjectException;
use Throwable;

class ContentRangeInvalidException extends ValueObjectException
{
    /**
     * @var int
     */
    private $from;

    /**
     * @var int
     */
    private $to;

    /**
     * @var int
     */
    private $length;

    public function __construct($from, $to, $length, int $code = 0, Throwable $previous = null)
    {
        $this->from   = $from;
        $this->to     = $to;
        $this->length = $length;

        parent::__construct('Content range invalid', $code, $previous);
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @return int
     */
    public function getTo(): int
    {
        return $this->to;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }
}
