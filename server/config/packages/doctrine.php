<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * Doctrine auto-mapping
 */

/**
 * @var ContainerInterface $container
 */

if (!\function_exists('getDirSubDirs')) {
    function getDirSubDirs($dir, &$results = array()) {
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
    function sortByLongestKey(array $array) {
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
    /**
     * Add DOMAIN subdirectory eg. "Entity" to the mapping list
     *
     * @param string $domainPath
     * @param string $mappingPath
     * @param string $domainName
     * @param string $dirName
     * @param string $configDirname
     * @param $mappings
     */
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
    /**
     * 1. Scan all DOMAINS.
     * 2. For each DOMAIN generate a ORM configuration for entities and value objects
     *
     * @return array
     */
    function generateDoctrineMappings(): array {
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

if (!function_exists('generateReplicasConfiguration')) {
    function generateReplicasConfiguration(): array {
        $replicas = [];

        foreach ($_SERVER as $var => $value) {
            if (substr($var, 0, strlen('DB_REPLICA_')) !== 'DB_REPLICA_') {
                continue;
            }

            $parts = explode('_', $var);

            if (count($parts) < 3) {
                throw new \Exception('"' . $var . '" name has invalid format');
            }

            $doctrineKey = strtolower(substr($var, strlen($parts[0] . '_' . $parts[1] . '_' . $parts[2] . '_')));
            $replicas[$parts[1] . '_' . $parts[2]][$doctrineKey] = $value;
        }

        foreach ($replicas as $replica => $configuration) {
            validateReplicaConfigurationFields($replica, $configuration);
        }

        return $replicas;
    }
}

function validateReplicaConfigurationFields(string $replicaName, array $configuration) {
    $expectedKeys = ['default_dbname', 'dbname', 'host', 'password', 'user', 'port', 'path'];

    foreach ($expectedKeys as $key) {
        if (!isset($configuration[$key])) {
            throw new \Exception(
                '"' . $replicaName . '" is missing configuration key "' . $key . '" ' .
                '(DB_' . $replicaName . '_' . strtoupper($key)  . ')'
            );
        }
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
    'charset'        => '%env(resolve:DATABASE_CHARSET)%',
    'default_table_options' => [
        'charset' => '%env(resolve:DATABASE_CHARSET)%',
        'collate' => '%env(resolve:DATABASE_COLLATE)%'
    ]
];

//
// Database URL or multiple parameters
//
if ($_SERVER['DATABASE_URL'] ?? '') {
    $dbalConfiguration['url'] = '%env(resolve:DATABASE_URL)%';
} else {
    $dbalConfiguration = \array_merge($dbalConfiguration, [
        'default_dbname' => '%env(resolve:DATABASE_NAME)%',
        'dbname'         => '%env(resolve:DATABASE_NAME)%',
        'host'           => '%env(resolve:DATABASE_HOST)%',
        'password'       => '%env(resolve:DATABASE_PASSWORD)%',
        'user'           => '%env(resolve:DATABASE_USER)%',
        'port'           => '%env(resolve:DATABASE_PORT)%',
        'path'           => '%env(resolve:DATABASE_PATH)%'
    ]);
}

if ($_SERVER['DB_REPLICATION'] ?? false) {
    $dbalConfiguration['slaves'] = generateReplicasConfiguration();
}

$container->loadFromExtension('doctrine', [
    'dbal' => $dbalConfiguration,
    'orm' => [
        'auto_generate_proxy_classes' => '%kernel.debug%',
        'naming_strategy'             => 'doctrine.orm.naming_strategy.underscore_number_aware',
        'auto_mapping'                => true,
        'mappings' => sortByLongestKey(generateDoctrineMappings()),
        'dql'      => [
            'string_functions' => [
                'cast' => Oro\ORM\Query\AST\Functions\Cast::class
            ]
        ]
    ]
]);
