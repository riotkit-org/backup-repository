<?php declare(strict_types=1);

namespace App\Domain\Replication\ValueObject;

use App\Domain\Common\ValueObject\BaseValueObject;

class EncryptionPassphrase extends BaseValueObject
{
    /**
     * @var string
     */
    private $value;

    /**
     * @codeCoverageIgnore Until no logic code
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @codeCoverageIgnore Until no logic code
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
