<?php

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_log',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USERNAME,
            'pass' => DB_PASSWORD,
            'port' => DB_PORT,
            'charset' => DB_CHARSET,
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USERNAME,
            'pass' => DB_PASSWORD,
            'port' => DB_PORT,
            'charset' => DB_CHARSET,
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USERNAME,
            'pass' => DB_PASSWORD,
            'port' => DB_PORT,
            'charset' => DB_CHARSET,
        ]
    ],
    'version_order' => 'creation'
];
