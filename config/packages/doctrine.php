<?php declare(strict_types=1);

/*
 * Doctrine auto-mapping
 */

if (!function_exists('generateDoctrineMappings')) {
    function generateDoctrineMappings(): array
    {
        $domains = glob(__DIR__ . '/../../src/Domain/*/Entity');
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

            $mappings[$domainName] = [
                'is_bundle' => false,
                'type'      => 'yml',
                'dir'       => $mappingPath,
                'prefix'    => 'App\\Domain\\' . $domainName . '\\Entity',
                'alias'     => 'App:' . $domainName
            ];
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
        'mappings' => generateDoctrineMappings()
    ]
]);
