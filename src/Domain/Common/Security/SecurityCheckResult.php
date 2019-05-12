<?php declare(strict_types=1);

namespace App\Domain\Common\Security;

class SecurityCheckResult
{
    /**
     * @var bool
     */
    private $status;

    /**
     * @var string
     */
    private $reason;

    public function __construct(bool $status, string $reason = '')
    {
        $this->status = $status;
        $this->reason = $reason;
    }

    public function isOk(): bool
    {
        return $this->status;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
