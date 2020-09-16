<?php

require_once __DIR__ . '/vendor/autoload.php';

use msb\model\Processing;
use msb\model\Schedule;

// Checking the schedule, whether someone needs to send a message
(new  Schedule())->check();

// Reply to all messages, once per second
$minute = gmdate("i");
$processing = new  Processing();
while ($minute == gmdate("i")) {
    echo PHP_EOL . gmdate("i:s");
    $processing->check();
    sleep(1);
}

// Let's create a mailing list for the day.
(new  Schedule())->generate();
