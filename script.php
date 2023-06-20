<?php

require_once('vendor/autoload.php');

use msb\model\Processing;
use msb\model\Schedule;

// Где будем хранить логи
$logFile = 'log/work.log';

// Проверяем, существует ли файл
if (!file_exists($logFile)) {
    // Создаем файл
    touch($logFile);
    chmod($logFile, 0777);
}

// Устанавливаем максимальное время выполнения скрипта в 60 секунд
set_time_limit(60);

// Бесконечный цикл, который будет повторяться после завершения
while (true) {
// Checking the schedule, whether someone needs to send a message
    (new  Schedule())->check();

// Reply to all messages, once per second
    $minute = gmdate("i");
    $processing = new  Processing();
    while ($minute == gmdate("i")) {
        echo PHP_EOL.gmdate("i:s");
        $processing->check();
        sleep(1);
    }

    // Let's create a mailing list for the day.
    (new  Schedule())->generate();

    // Завершаем текущую итерацию, чтобы избежать нагрузки на сервер
    sleep(1); // Задержка 1 секунда перед каждой итерацией цикла

    // Определяем текущее время
    $currentTime = time();

    // Проверяем, если прошла минута, завершаем скрипт и перезапускаем его
    if ($currentTime - $_SERVER['REQUEST_TIME'] >= 60) {
        // Запускаем новый экземпляр скрипта
        exec('php '.__FILE__.' >> /app/log/error.log 2>&1 &');
        exit(); // Завершаем текущий экземпляр скрипта
    }
}