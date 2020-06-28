<?php

namespace msb\command;

use msb\core\DB;
use Telegram;

class Start
{
    private Telegram $telegram;
    private int $chat_id = 0;
    private DB $db;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function index()
    {
        $this->db->addSchedule(
            [
                'chat_id' => $this->chat_id,
                'hour_start' => 9,
                'hour_end' => 14,
                'time_zone_offset' => 3,
                'quantity' => 1,
            ]
        );

        $message[] = "Hello!";
        $message[] = "How it works: The bot sends you a message to remember every day at periodic intervals.";

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        $message = [];
        $message[] = "To add a message to repeat, just send me a message.";
        $message[] = "You can also edit any message you send in the usual way for telegrams.";
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        $message = [];
        $message[] = "To receive a message to repeat right now: /now";
        $message[] = "The time interval and the number of messages you can configure in the settings: /setting";
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );
    }
}
