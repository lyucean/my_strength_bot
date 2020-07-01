<?php


// wrapper for Yandex metrics

/**
 * @param $id
 * @param $command
 */
function ya_metric($id, $command)
{
    $context = stream_context_create(
        [
            'http' => [
                'method' => 'GET',
                'header' => 'Accept-language: en' . PHP_EOL .
                    'Content-Type: application/javascript' . PHP_EOL .
                    'Cookie: foo=bar' . PHP_EOL .
                    'User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us)' . PHP_EOL
            ],
        ]
    );

    // cut parameters
    if (preg_match('/^\/_[0-9]$/', $command)) {
        $command = '/_';
    }

    // cut parameters
    if ($str_pos = strpos($command, "?") !== false) {
        $command = substr($command, 0, $str_pos);
    }

    $url = 'https://mc.yandex.ru/watch/' . YANDEX_METRIC_ID;
    $params = [
        'wmode' => 7,
        'page-ref' => $id,
        'page-url' => $command,
        'charset' => 'utf-8',
    ];
    @file_get_contents($url . '?' . http_build_query($params), false, $context);
}
