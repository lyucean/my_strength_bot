<?php


namespace msb\command;

use Telegram;

class Menu
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
        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    '🧾 Каталог сообщений',
                    $url = '',
                    '/catalog'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    '👩🏻‍🔧 Настройки',
                    $url = '',
                    '/setting'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'ℹ Как это работает?',
                    $url = '',
                    '/faq'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    '🆘 Пожаловаться на баг',
                    $url = '',
                    '/help'
                )
            ],
        ];

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Меню'
            ]
        );
    }
}
