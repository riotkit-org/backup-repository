<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

use App\Domain\Backup\Exception\ValueObjectException;

class HttpMethod implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    public const METHOD_GET    = 'GET';
    public const METHOD_POST   = 'POST';
    public const METHOD_PUT    = 'PUT';
    public const METHOD_PATCH  = 'PATCH';
    public const METHOD_DELETE = 'DELETE';

    public const METHODS = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_PATCH,
        self::METHOD_DELETE
    ];

    /**
     * @param string $methodName
     *
     * @throws ValueObjectException
     */
    public function __construct(string $methodName)
    {
        if (!\in_array($methodName, self::METHODS, true)) {
            throw new ValueObjectException('Unknown HTTP method "' . $methodName . '"');
        }

        $this->value = $methodName;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
