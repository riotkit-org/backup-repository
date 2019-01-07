<?php declare(strict_types=1);

if (!defined('FS_MAPPING')) {
    define('FS_MAPPING', [
        'local' => [
            'directory'    => ['%kernel.root_dir%/uploads', 'string'],
            'lazy'         => [null, 'bool'],
            'writeFlags'   => [null, 'string'],
            'linkHandling' => [null, 'string'],
            'permissions'  => [null, 'string']
        ],

        'awss3' => [
            'client' => ['s3_client', 'string'],
            'bucket' => [null, 'string'],
            'prefix' => [null, 'string']
        ],

        'ftp' => [
            'host'        => ['localhost', 'string'],
            'port'        => [21, 'integer'],
            'username'    => [null, 'string'],
            'password'    => [null, 'string'],
            'root'        => [null, 'string'],
            'ssl'         => [null, 'bool'],
            'timeout'     => [null, 'integer'],
            'permPrivate' => [null, 'string'],
            'permPublic'  => [null, 'string'],
            'passive'     => [null, 'bool']
        ]
    ]);

    function mappingToEnvVariables()
    {
        $vars = [];
        LOCK_NB;

        foreach (FS_MAPPING as $adapterName => $options) {
            foreach ($options as $option => $defaultValue) {
                $vars[] = 'FS_' . \strtoupper($adapterName) . '_' . \strtoupper($option);
            }
        }

        return $vars;
    }
}

$adapters = [
    'default_adapter' => [
        // this will be populated from environment variables by the foreach later
    ]
];

$adapterName = strtolower((string) getenv('FS_ADAPTER'));

if (!array_key_exists($adapterName, FS_MAPPING)) {
    throw new \InvalidArgumentException(
        "FS_ADAPTER have invalid value, possible values: " . \implode(', ', array_keys(FS_MAPPING)) . ".\n\nPossible environment variables: \n" . \implode(", \n", mappingToEnvVariables()) . "\n"
    );
}

foreach (FS_MAPPING[$adapterName] as $option => $details) {
    [$value, $type] = $details;

    $envName = 'FS_' . \strtoupper($adapterName) . '_' . \strtoupper($option);

    if (getenv($envName)) {
        $value = \getenv($envName);
    }

    if ($type === 'bool') {
        $value = (bool) $value;
    }

    elseif ($type === 'integer') {
        $value = (int) $value;
    }

    $adapters['default_adapter'][$adapterName][$option] = $value;
}

// advanced usage: allow to unpack a JSON
if (getenv('FS_JSON')) {
    $adapters = \array_merge($adapters, \json_decode(\getenv('FS_JSON'), true));
}

$container->loadFromExtension('oneup_flysystem', [
    'adapters' => $adapters,
    'filesystems' => [
        'default_filesystem' => [
            'adapter' => 'default_adapter',
            'alias'   => 'League\\Flysystem\\Filesystem'
        ]
    ]
]);
