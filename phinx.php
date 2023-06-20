<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return
  [
    'paths' => [
      'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
      'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
      'default_migration_table' => 'phinxlog',
      'default_environment' => 'production ',
      'production ' => [
        'adapter' => 'mysql',
        'host' => $_ENV['PMA_HOST'],
        'name' => $_ENV['MYSQL_DATABASE'],
        'user' => $_ENV['MYSQL_USER'],
        'pass' => $_ENV['MYSQL_PASSWORD'],
        'port' => '3306',
        'charset' => 'utf8',
      ],
    ],
    'version_order' => 'creation'
  ];
