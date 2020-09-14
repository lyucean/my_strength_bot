<?php

// simple division into prod and dev
// load config, overwrite existing environment variables
if (empty($_SERVER['OS'])) {
    $dotenv = Dotenv\Dotenv::createMutable(__DIR__);
} else {
    $dotenv = Dotenv\Dotenv::createMutable(__DIR__, '.env.dev');
}
$dotenv->load();

$dotenv->required('PROJECT_NAME')->notEmpty();
$dotenv->required('PROJECT_VERSION')->notEmpty();

$dotenv->required('DIR_COMMAND')->notEmpty();
$dotenv->required('DIR_FILE')->notEmpty();

$dotenv->required('TELEGRAM_TOKEN')->notEmpty();
$dotenv->required('TELEGRAM_TEST_CHAT_ID')->notEmpty();

$dotenv->required('DB_HOST');
$dotenv->required('DB_USERNAME');
$dotenv->required('DB_PASSWORD');
$dotenv->required('DB_NAME');
$dotenv->required('DB_PORT');
$dotenv->required('DB_CHARSET');
$dotenv->required('DB_NAME_PHINX_LOG');

$dotenv->required('MAX_OF_MESSAGES_PER_DAY');
$dotenv->required('MAX_LINE_LENGTH');
