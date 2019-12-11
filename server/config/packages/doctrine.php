<?php declare(strict_types=1);

/*
 * Doctrine auto-mapping
 */

use App\Infrastructure\Common\Service\PostgreSQLDoctrineDriver;

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
$databaseDriver = $_SERVER['DATABASE_DRIVER'] ?? 'pdo_sqlite';
$defaultsForSupportedDrivers = [
    'pdo_mysql' => [
        'env(DATABASE_VERSION)' => '5.8',
        'env(DATABASE_CHARSET)' => 'utf8mb4',
        'env(DATABASE_COLLATE)' => 'utf8mb4_unicode_ci',
        'env(DATABASE_PORT)'    => '3306'
    ],
    'pdo_pgsql' => [
        'env(DATABASE_VERSION)' => '10.10',
        'env(DATABASE_CHARSET)' => 'UTF-8',
        'env(DATABASE_COLLATE)' => 'pl_PL.UTF-8',
        'env(DATABASE_PORT)'    => '3306'
    ]
];

$driverDefaults = $defaultsForSupportedDrivers[$databaseDriver] ?? [];

$container->setParameter('env(DATABASE_URL)', '');
$container->setParameter('env(DATABASE_DRIVER)', 'pdo_sqlite');
$container->setParameter('env(DATABASE_VERSION)', $driverDefaults['env(DATABASE_VERSION)'] ?? '');
$container->setParameter('env(DATABASE_CHARSET)', $driverDefaults['env(DATABASE_CHARSET)'] ?? '');
$container->setParameter('env(DATABASE_COLLATE)', $driverDefaults['env(DATABASE_COLLATE)'] ?? '');
$container->setParameter('env(DATABASE_PORT)',    $driverDefaults['env(DATABASE_PORT)'] ?? '');
$container->setParameter('env(DATABASE_NAME)', 'riotkit_filerepository');
$container->setParameter('env(DATABASE_HOST)', 'localhost');
$container->setParameter('env(DATABASE_PASSWORD)', '');
$container->setParameter('env(DATABASE_USER)', 'riotkit');

// sqlite3
$container->setParameter('env(DATABASE_PATH)', './var/data.db');

$dbalConfiguration = [
    'driver'         => '%env(resolve:DATABASE_DRIVER)%',
    'server_version' => '%env(resolve:DATABASE_VERSION)%',
    'charset'        => '%env(DATABASE_CHARSET)%',
    'default_table_options' => [
        'charset' => '%env(DATABASE_CHARSET)%',
        'collate' => '%env(DATABASE_COLLATE)%'
    ],
    'default_dbname' => '%env(resolve:DATABASE_NAME)%',
    'dbname'   => '%env(resolve:DATABASE_NAME)%',
    'host'     => '%env(resolve:DATABASE_HOST)%',
    'password' => '%env(resolve:DATABASE_PASSWORD)%',
    'user'     => '%env(resolve:DATABASE_USER)%',
    'port'     => '%env(resolve:DATABASE_PORT)%',
    'path'     => '%env(resolve:DATABASE_PATH)%'
];

if ($_SERVER['DATABASE_URL'] ?? '') {
    $dbalConfiguration['url'] = '%env(resolve:DATABASE_URL)%';
}

$container->loadFromExtension('doctrine', [
    'dbal' => $dbalConfiguration,
    'orm' => [
        'auto_generate_proxy_classes' => '%kernel.debug%',
        'naming_strategy'             => 'doctrine.orm.naming_strategy.underscore',
        'auto_mapping'                => true,
        'mappings' => sortByLongestKey(generateDoctrineMappings())
    ]
]);

