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

        $message[] = 'Привет!';
        $message[] = 'Как это работает:';
        $message[] = 'Ты отправляешь мне сообщения с текстом или картинкой, которые делают тебя сильнее.';
        $message[] = 'Я сохраняю их и отправляю тебе обратно, каждый день, в удобный для тебя интервал.';

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        $message = [];
        $message[] = 'Чтобы добавить сообщение для повторения, просто отправьте мне сообщение.';
        $message[] = "Ты также можешь редактировать любое сообщение, обычным способом для телеграмм.";
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );

        $message = [];
        $message[] = "Чтобы получить сообщение, чтобы прямо сейчас: /now";
        $message[] = "Временной интервал и количество сообщений вы можете настроить в настройках: /setting";
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => implode("\n", $message)
            ]
        );
    }
}
