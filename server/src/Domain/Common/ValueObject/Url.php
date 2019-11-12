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

        $this->value = $this->normalize($this->value, true);
        $normalized = $this->normalize($this->value, false);

        if ($this->value && !filter_var($normalized, FILTER_VALIDATE_URL)) {
            $exceptionType = static::getExceptionType();
            throw new $exceptionType('Invalid URL address: ' . $normalized);
        }
    }

    /**
     * @codeCoverageIgnore
     *
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

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
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
        $new->value = \str_ireplace('{' . $name . '}', $value, $this->value);
        $new->value = \preg_replace('/{{\s*' . \preg_quote($name, '/') . '\s*}}/', $value, $new->value);

        return $new;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return static
     */
    public function withQueryParam(string $key, string $value)
    {
        $parsed = parse_url($this->value);
        $urlWithoutQS = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '')
            . (isset($parsed['port']) ? ':' . $parsed['port'] : '')
            . ($parsed['path'] ?? '/');

        $params = [];
        parse_str($parsed['query'] ?? '', $params);
        $params[$key] = $value;


        $new = clone $this;
        $new->value = $urlWithoutQS . '?' . http_build_query($params);

        return $new;
    }

    public function isLocalFileUrl(): bool
    {
        return strtolower(parse_url($this->getValue(), PHP_URL_SCHEME)) === 'file';
    }

    private function normalize(string $value, bool $correctTemplateVariables)
    {
        $normalized = (new Normalizer($value))->normalize();

        if ($correctTemplateVariables) {
            $normalized = str_replace('%20', '', $normalized);
            $normalized = preg_replace('/\%7B\%7B(\w+)\%7D\%7D/', '{{ $1 }}', $normalized);
            $normalized = preg_replace('/\%7B(\w+)\%7D/', '{{ $1 }}', $normalized);
        }

        return $normalized;
    }
}
