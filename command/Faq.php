<?php


namespace msb\command;

use Telegram;

class Faq
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
                'text' => "Я твой личный библиотекарь всего того, что даёт вам поддержку, " .
                    "мотивацию, делает сильнее, поднимает настроение. " .
                    "Это могут быть  цитаты, мысли, фото, видео. Присылай всё мне." .
                    PHP_EOL .
                    "Я сохраняю их и отправляю обратно, по одной штуке в день, каждый день, в удобный для тебя интервал." .
                    "Так, каждый день, вы будете получать то, маленькую поддержку, от самого себя."
            ]
        );
    }
}
