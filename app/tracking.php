<?php

if (OC_ENV_PROD) {
    $notifier = new Airbrake\Notifier(
        array(
            'projectId' => AIR_BRAKE_PROJECT_ID,
            "environment" => ENVIRONMENT,
            'projectKey' => AIR_BRAKE_PROJECT_KEY
        )
    );

    Airbrake\Instance::set($notifier);

    $handler = new Airbrake\ErrorHandler($notifier);
    $handler->register();
}

//try {
//    throw new Exception('Test');
//} catch (Exception $e) {
//    Airbrake\Instance::notify($e);
//}

