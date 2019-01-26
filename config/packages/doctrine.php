<?php declare(strict_types=1);

/*
 * Doctrine auto-mapping
 */

if (!\function_exists('getDirSubDirs')) {
    function getDirSubDirs($dir, &$results = array())
    {
        $files = \scandir($dir, SCANDIR_SORT_NONE);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (is_dir($path)) {
                if ($value !== "." && $value !== "..") {
                    getDirSubDirs($path, $results);
                    $results[] = $path;
                }
            }
        }

        return $results;
    }
}

if (!\function_exists('sortByLongestKey')) {
    function sortByLongestKey(array $array)
    {
        $keys = [];
        $results = [];

        foreach ($array as $name => $value) {
            $keys[$name] = \strlen($name);
        }

        arsort($keys);

        foreach ($keys as $key => $length) {
            $results[$key] = $array[$key];
        }

        return $results;
    }
}

if (!\function_exists('addDoctrineMappings')) {
    function addDoctrineMappings(
        string $domainPath,
        string $mappingPath,
        string $domainName,
        string $dirName,
        string $configDirname,
        &$mappings
    ) {
        if (!is_dir($domainPath . '/' . $dirName . '/')) {
            return;
        }

        $mappings[$domainName . '_' . $dirName] = [
            'is_bundle' => false,
            'type' => 'yml',
            'dir' => $mappingPath . '/' . $configDirname,
            'prefix' => 'App\\Domain\\' . $domainName . '\\' . $dirName,
            'alias' => 'App:' . $domainName . ':' . $dirName
        ];

        $subDirs = getDirSubDirs($domainPath . '/' . $dirName . '/');

        foreach ($subDirs as $subDir) {
            $split = explode('/' . $dirName . '/', $subDir);
            $subName = $split[1];

            $mappings[$domainName . '_' . $dirName . '_' . $subName] = [
                'is_bundle' => false,
                'type' => 'yml',
                'dir' => $mappingPath . '/' . $configDirname . '/' . strtolower($subName),
                'prefix' => 'App\\Domain\\' . $domainName . '\\' . $dirName . '\\' . str_replace('/', '\\', $subName),
                'alias' => 'App:' . $domainName . ':' . $dirName . ':' . str_replace('/', ':', $subName)
            ];
        }
    }
}

if (!function_exists('generateDoctrineMappings')) {
    function generateDoctrineMappings(): array
    {
        $domains = glob(__DIR__ . '/../../src/Domain/*/');
        $rootDir = dirname(__DIR__, 2) . '/';
        $mappings = [];

        foreach ($domains as $domain) {
            $fwPath = str_replace($rootDir, '%kernel.project_dir%/', realpath($domain));
            $exp = explode('/', $fwPath);

            if (!isset($exp[3])) {
                throw new LogicException('Cannot find domain name for path ' . $fwPath);
            }

            $domainName = $exp[3];

            // create a mapping path
            $mappingPath = str_replace('%/src/Domain/', '%/config/orm/', $fwPath);
            $secondPart = explode('/config/orm', $mappingPath)[1];
            $mappingPath = str_replace($secondPart, strtolower($secondPart), $mappingPath);

            addDoctrineMappings($domain, $mappingPath, $domainName, 'Entity', 'entity', $mappings);
            addDoctrineMappings($domain, $mappingPath, $domainName, 'ValueObject', 'valueobject', $mappings);
        }

        return $mappings;
    }
}

# Adds a fallback DATABASE_URL if the env var is not set.
# This allows you to run cache:warmup even if your
# environment variables are not available yet.
# You should not need to change this value.
$container->setParameter('env(DATABASE_URL)', '');

$container->loadFromExtension('doctrine', [
    'dbal' => [
        'driver'         => 'pdo_mysql',
        'server_version' => '5.7',
        'charset'        => 'utf8mb4',
        'default_table_options' => [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci'
        ],
        'url' => '%env(resolve:DATABASE_URL)%'
    ],

    'orm' => [
        'auto_generate_proxy_classes' => '%kernel.debug%',
        'naming_strategy'             => 'doctrine.orm.naming_strategy.underscore',
        'auto_mapping'                => true,
        'mappings' => sortByLongestKey(generateDoctrineMappings())
    ]
]);
