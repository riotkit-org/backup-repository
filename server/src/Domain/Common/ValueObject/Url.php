<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonValueException;
use URL\Normalizer;

class Url extends BaseValueObject implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     * @param BaseUrl|null $baseUrl
     *
     * @throws CommonValueException
     */
    public function __construct(string $value, BaseUrl $baseUrl = null)
    {
        $this->value = $value;
        $original = $value;

        if ($baseUrl) {
            $this->value = $original = $baseUrl->getValue() . '/' . $this->value;
        }

        $this->value = $this->normalize($this->value, true);
        $normalized = $this->normalize($this->value, false);

        if ($this->value && !filter_var($normalized, FILTER_VALIDATE_URL)) {
            throw CommonValueException::fromInvalidUrl($normalized, $original);
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @param Url $url
     *
     * @return static
     *
     * @throws CommonValueException
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

    public function withVar(string $name, string $value): Url
    {
        $new = clone $this;
        $new->value = \str_ireplace('{' . $name . '}', $value, $this->value);
        $new->value = \preg_replace('/{{\s*' . \preg_quote($name, '/') . '\s*}}/', $value, $new->value);

        return $new;
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
