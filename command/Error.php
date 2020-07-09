<?php


namespace msb\command;

use Exception;
use Telegram;

class Error
{
    private Telegram $telegram;
    private int $chat_id;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
    }

    public function send($message, $throw = true)
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => '👮🏻‍♀️ ' . $message
            ]
        );

        if ($throw) {
            $message = '[' . $this->telegram->getUpdateType() . '] ' . $message;
            new Exception($message);
        }
    }
}
