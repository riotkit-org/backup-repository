<?php declare(strict_types=1);

namespace App\Domain\Common\Service;

use Symfony\Component\Yaml\Yaml;

class Versioning
{
    /**
     * @var string
     */
    private $version;

    private $filePath = __DIR__ . '/../../../../config/version.yaml';

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
