<?php declare(strict_types=1);

use League\Flysystem\Filesystem;

require_once __DIR__ . '/../../src/Infrastructure/Common/Service/ConfigParser.php';

$configParser = new \App\Infrastructure\Common\Service\ConfigParser([
    'local' => [
        'directory'    => ['%kernel.root_dir%/uploads', 'string'],
        'lazy'         => [null, 'bool'],
        'writeFlags'   => [null, 'string'],
        'linkHandling' => [null, 'string'],
        'permissions'  => [null, 'string']
    ],

    'awss3v3' => [
        'client'  => ['s3_client', 'string'],
        'bucket'  => [null, 'string'],
        'prefix'  => [null, 'string'],
        'options' => [
            [
                'endpoint'                    => [null, 'string'],
                'override_visibility_on_copy' => [null, 'bool']
            ]
        ]
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

$adapters = $configParser->buildAdapters();
$filesystems = [
    'readwrite' => [
        'adapter' => 'default_adapter',
        'alias'   => Filesystem::class
    ],

    // we want to always have READ-ONLY filesystem, even if it may be the same as READ-WRITE filesystem
    // just for later simplification of the code. We assume the RO filesystem is always present
    'readonly' => [
        'adapter' => isset($adapters['ro_adapter']) ? 'ro_adapter' : 'default_adapter',
        'alias'   => Filesystem::class
    ]
];

$container->loadFromExtension('oneup_flysystem', [
    'adapters' => $adapters,
    'filesystems' => $filesystems
]);
