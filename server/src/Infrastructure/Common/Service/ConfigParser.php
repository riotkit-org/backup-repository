<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

class ConfigParser
{
    /**
     * @var array
     */
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function mapToEnvironmentVariables(string $suffix): array
    {
        $vars = [];

        foreach ($this->mapping as $adapterName => $options) {
            foreach ($options as $option => $defaultValue) {
                $vars[] = $suffix . \strtoupper($adapterName) . '_' . \strtoupper($option);
            }
        }

        return $vars;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getPossibleAdapters(): array
    {
        return \array_keys($this->getMapping());
    }

    public function buildAdapters(): array
    {
        $adapters = ['default_adapter' => $this->buildAdapter('FS_')];

        if ($this->getValue('FS_RO_ADAPTER')) {
            $adapters['ro_adapter'] = $this->buildAdapter('FS_RO_');
        }

        // advanced usage: allow to unpack a JSON
        if ($this->getValue('FS_JSON')) {
            $adapters = \array_merge($adapters, \json_decode(\getenv('FS_JSON'), true));
        }

        return $adapters;
    }

    protected function buildAdapter(string $suffix): array
    {
        $adapterName = strtolower((string) $this->getValue($suffix. 'ADAPTER', 'local'));

        if (!\array_key_exists($adapterName, $this->getMapping())) {
            throw new \InvalidArgumentException(
                $suffix . 'ADAPTER has invalid value, possible values: ' .
                \implode(', ', $this->getPossibleAdapters()) . ".\n\nPossible environment variables: \n" .
                \implode(", \n", $this->mapToEnvironmentVariables($suffix)) . "\n"
            );
        }

        return [
            $adapterName => $this->buildOptions($suffix . \strtoupper($adapterName), $this->mapping[$adapterName])
        ];
    }

    protected function buildOptions(string $envPath, array $optionsDefinitions): array
    {
        $optionsFilledUp = [];

        foreach ($optionsDefinitions as $option => $details) {
            $envName = $envPath . '_' . \strtoupper($option);

            if (\count($details) === 1 && \is_array($details[0])) {
                $optionsFilledUp[$option] = $this->buildOptions($envName, $details[0]);
                continue;
            }

            [$value, $type] = $details;

            if (getenv($envName)) {
                $value = \getenv($envName);
            }

            if ($type === 'bool') {
                $value = (bool) $value;
            }

            elseif ($type === 'integer') {
                $value = (int) $value;
            }

            $optionsFilledUp[$option] = $value;
        }

        return $optionsFilledUp;
    }

    protected function getValue(string $envName, $default = '')
    {
        return getenv($envName) ?: $default;
    }
}
