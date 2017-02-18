<?php declare(strict_types=1);

namespace Model;

/**
 * @package Model
 */
class AllowedMimeTypes
{
    /**
     * Types whitelisted in application configuration
     *
     * @var string[] $whitelisted
     */
    private $whitelisted = [];

    /**
     * Types requested dynamically
     *
     * @var string[] $requested
     */
    private $requested = [];

    /**
     * @var string[] $combined
     */
    private $combined = [];

    /**
     * @param array $whitelisted
     * @param array $requested
     */
    public function __construct(array $whitelisted, array $requested)
    {
        $this->whitelisted = $whitelisted;
        $this->requested   = $requested;
        $this->build();
    }

    private function build()
    {
        // fallback to global defaults
        if (empty($this->requested)) {
            $this->combined = $this->whitelisted;
            return;
        }

        $this->combined = [];

        foreach ($this->requested as $extension => $mime) {
            if (isset($this->whitelisted[$extension]) && $this->whitelisted[$extension] === $mime) {
                $this->combined[$extension] = $mime;
            }
        }
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->combined;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return implode(', ', array_values($this->all()));
    }
}
