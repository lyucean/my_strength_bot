<?php


namespace msb\command;

use Telegram;

class Help
{
    private Telegram $telegram;
    private int $chat_id;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
    }

    public function index()
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Если вам необходима тех. поддержка, вы может написать мне @lyucean'
            ]
        );
    }
}

