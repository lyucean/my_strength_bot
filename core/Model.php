<?php


namespace msb\core;

use Telegram;

class Model
{
    public Telegram $telegram;
    public DB $db;

    public function __construct()
    {
        $this->telegram = new Telegram(
            $_ENV['TELEGRAM_TOKEN'],
            true,
            //            [
            //                'type' => PROXY_TYPE,
            //                'auth' => PROXY_AUTH,
            //                'url' => PROXY_IP,
            //                'port' => PROXY_PORT,
            //            ]
        );
        $this->db = new DB();
    }
}
