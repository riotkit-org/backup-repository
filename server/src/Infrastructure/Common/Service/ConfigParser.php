<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

use App\FilesystemConfigDefinition;

class ConfigParser
{
    private array $mapping;

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
        $readWriteAdapterName = $this->getValue('FS_RW_NAME', 'RW');
        $readOnlyAdapterName = $this->getValue('FS_RO_NAME', '');

        $adapters = ['default_adapter' => $this->buildAdapter('FS_' . $readWriteAdapterName)];

        if ($this->getValue('FS_' . $readOnlyAdapterName . '_ADAPTER')) {
            $adapters['ro_adapter'] = $this->buildAdapter('FS_' . $readOnlyAdapterName);
        }

        // advanced usage: allow to unpack a JSON
        if ($this->getValue('FS_JSON')) {
            $adapters = \array_merge($adapters, \json_decode(\getenv('FS_JSON'), true));
        }

        return $adapters;
    }

    protected function buildAdapter(string $suffix): array
    {
        $adapterName = strtolower((string) $this->getValue($suffix. '_ADAPTER', 'local'));

        if (!\array_key_exists($adapterName, $this->getMapping())) {
            throw new \InvalidArgumentException(
                $suffix . '_ADAPTER has invalid value, possible values: ' .
                \implode(', ', $this->getPossibleAdapters()) . ".\n\nPossible environment variables: \n" .
                \implode(", \n", $this->mapToEnvironmentVariables($suffix)) . "\n"
            );
        }

        $options = $this->buildOptions($suffix, $this->mapping[$adapterName], true)[0];

        if (isset($options['client']) && !$options['client']) {
            $options['client'] = strtolower($suffix . 'client');
        }

        return [
            'adapter'      => $adapterName,
            'options'      => $options,
            'adapter_args' => $this->buildOptions($suffix, $this->mapping[$adapterName], false)[0]
        ];
    }

    /**
     * Parses environment variables from system and puts in schema defined in $optionsDefinitions
     * so the Flysystem and Storage Adapter will get values as valid arrays that they understand (schema defines format)
     *
     * Secondly the environment variables are validated - redundant env variables cannot exist, error will be raised
     * ex. FS_MYFS_INVALIDNAME will raise that INVALIDNAME is not a name valid in schema
     *
     * @see FilesystemConfigDefinition
     *
     * @param string $envPath               Prefix
     * @param array $optionsDefinitions     Definitions from FilesystemConfigDefinition
     * @param bool $lookingForOptionNotArg  Are we looking for $options (flysystem adapter) or $args (client - S3Client/GoogleStorageClient/other)
     *
     * @return array
     * @throws \Exception
     */
    protected function buildOptions(string $envPath, array $optionsDefinitions, bool $lookingForOptionNotArg): array
    {
        $optionsFilledUp = [];
        $recognizedEnvs = [];

        foreach ($optionsDefinitions as $option => $details) {
            $envName = $envPath . '_' . \strtoupper($option);

            $value = $details[0];
            $type = $details[1] ?? null;
            $when = $details[2] ?? true;

            if (\is_array($details[0])) {
                [$opts, $envs] = $this->buildOptions($envName, $value, $lookingForOptionNotArg);

                // even we look ex. for $args we cannot forget that env variables are still valid for $options
                if ($lookingForOptionNotArg && !$when || !$lookingForOptionNotArg && $when) {
                    $recognizedEnvs = array_merge($recognizedEnvs, $envs);
                    continue;
                }

                $optionsFilledUp[$option] = $opts;
                $recognizedEnvs = array_merge($recognizedEnvs, $envs);

                continue;
            }

            // options vs args
            if ($lookingForOptionNotArg && !$when || !$lookingForOptionNotArg && $when) {
                $recognizedEnvs[] = $envName;
                continue;
            }

            if (getenv($envName)) {
                $value = \getenv($envName);
            }

            if ($type === 'bool') {
                $value = \in_array(strtolower((string) $value), ['true', 'TRUE', '1', 'Y'], true);
            }

            elseif ($type === 'integer') {
                $value = (int) $value;
            }

            $optionsFilledUp[$option] = $value;
            $recognizedEnvs[] = $envName;
        }

        foreach (array_keys($_SERVER) as $envName) {
            if (strpos($envName, $envPath . '_') !== 0) {
                continue;
            }

            // special vars
            if ($envName === $envPath . '_ADAPTER') {
                continue;
            }

            if (!in_array($envName, $recognizedEnvs, true)) {
                throw new \Exception('Unrecognized environment variable "' . $envName . '"');
            }
        }

        return [$optionsFilledUp, $recognizedEnvs];
    }

    protected function getValue(string $envName, $default = '')
    {
        return getenv($envName) ?: $default;
    }
}
