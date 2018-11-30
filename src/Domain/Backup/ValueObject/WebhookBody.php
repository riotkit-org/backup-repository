<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

class WebhookBody
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $body)
    {
        $this->value = $body;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
