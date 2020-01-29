<?php declare(strict_types=1);

namespace App\Domain\Common\Service;

use Symfony\Component\Yaml\Yaml;

/**
 * NOTICE: No dependency injection support. The __construct() cannot use DI as we want to use this class on very low level.
 */
class Versioning
{
    private ?string $version = null;
    private string $filePath = __DIR__ . '/../../../../config/version.yaml';

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getVersion(): string
    {
        if ($this->version) {
            return $this->version;
        }

        $file = Yaml::parseFile($this->filePath);

        if (!$file || !\is_file($this->filePath) || !isset($file['version'])) {
            throw new \Exception($this->filePath . ' has invalid structure, or does not exist');
        }

        return $file['version'];
    }
}
