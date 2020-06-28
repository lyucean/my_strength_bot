<?php


namespace msb\command;

use Exception;
use Telegram;

class Error
{
    private Telegram $telegram;
    private $chat_id;

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
                'text' => 'Error: ' . $message
            ]
        );

        // on dev always throw an Exception for testing
        if (OC_ENV_DEV ||
            $throw) {
            $message = '[' . $this->telegram->getUpdateType() . '] ' . $message;
            throw new Exception($message);
        }
    }
}
