<?php

namespace Service;

/**
 * @package Service
 */
class Versioning
{
    /** @var int $version */
    private $version = null;

    /** @var string $release */
    private $release = null;

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getReleaseNumber() .
                ($this->getVersionNumber() > 0 ? '.' . $this->getVersionNumber() : '');
    }

    /**
     * @return float
     */
    public function getVersionNumber(): float
    {
        $path = __DIR__ . '/../../var/version-number';

        if ($this->version === null) {
            $this->version = is_file($path) ? (int)file_get_contents($path) : 0;
        }

        return (float)$this->version;
    }

    /**
     * @return string
     */
    public function getReleaseNumber()
    {
        $path = __DIR__ . '/../../var/version-release';

        if ($this->release === null) {
            $this->release = is_file($path) ? (string)file_get_contents($path) : 0;
        }

        return $this->release;
    }
}