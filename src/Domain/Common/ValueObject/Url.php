<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use URL\Normalizer;

class Url extends BaseValueObject implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value, BaseUrl $baseUrl = null)
    {
        $this->value = $value;

        if ($baseUrl) {
            $this->value = $baseUrl->getValue() . '/' . $this->value;
        }

        $this->value = $this->normalize($this->value);

        if ($this->value && !filter_var($this->value, FILTER_VALIDATE_URL)) {
            $exceptionType = static::getExceptionType();
            throw new $exceptionType('Invalid URL address');
        }
    }

    /**
     * @param Url $url
     *
     * @return static
     */
    public static function fromBasicVersion($url)
    {
        return new static($url->getValue());
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }

    public function getReproducibleHash(): string
    {
        return hash('sha256', $this->value);
    }

    public function withVar(string $name, string $value): Url
    {
        $new = clone $this;
        $new->value = str_ireplace('{' . $name . '}', $value, $this->value);

        return $new;
    }

    public function isLocalFileUrl(): bool
    {
        return strtolower(parse_url($this->getValue(), PHP_URL_SCHEME)) === 'file';
    }

    private function normalize(string $value)
    {
        return (new Normalizer($value))->normalize();
    }
}
