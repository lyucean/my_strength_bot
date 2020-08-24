<?php

namespace msb\command;

use msb\core\DB;
use Telegram;

class Message
{
    private Telegram $telegram;
    private int $chat_id;
    private int $message_id = 0;
    private DB $db;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function __debugInfo()
    {
        return [
            'message_id' => $this->message_id,
        ];
    }

    public function edit()
    {
        $text = $this->telegram->Text();

        if (!empty($this->telegram->Caption())) {
            $text = $this->telegram->Caption();
        }

        $this->db->editMessageByMessageId(
            [
                'chat_id' => $this->chat_id,
                'text' => $text,
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Я сохранила изменения.'
            ]
        );
    }

    public function addImage()
    {
        // take the highest resolution
        $array = $this->telegram->Photo();
        $file = $this->telegram->getFile(array_pop($array)['file_id']);

        if (!array_key_exists('ok', $file) || !array_key_exists('result', $file)) {
            (new Error($this->telegram))->send('Я не смог скачать картинку, сервер недоступен.');
        }

        $file_path = $file['result']['file_path'];
        $file_name = $file['result']['file_unique_id'] . '.jpg';

        $url_on_server = 'https://api.telegram.org/file/bot' . TELEGRAM_TOKEN . '/' . $file_path;

        $folder = rand(10, 999) . '/';

        if (!is_dir(DIR_FILE . $folder)) {
            mkdir(DIR_FILE . $folder);
        }

        file_put_contents(DIR_FILE . $folder . $file_name, file_get_contents($url_on_server));

        $this->message_id = $this->telegram->MessageID();

        $this->db->addMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Caption(),
                'image' => $folder . $file_name,
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Отменить',
                    $url = '',
                    '/message/cancel?message_id=' . $this->message_id
                ),
            ],
        ];

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Я сохранила /_' . $this->message_id . ' 👩🏽‍'
            ]
        );
    }

    public function add()
    {
        if (!in_array($this->telegram->getUpdateType(), ['message', 'reply_to_message'])) {
            (new Error($this->telegram))->send(
                'Я не знаю, как работать с этим типом сообщений 🤷🏻'
            );
            return;
        }

        // double check
        if ($this->db->existCheckMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
            ]
        )) {
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => 'Это сообщение уже существует 👮🏻‍♀️'
                ]
            );
            return;
        }

        $this->message_id = $this->telegram->MessageID();

        $this->db->addMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Отменить',
                    $url = '',
                    '/message/cancel?message_id=' . $this->message_id
                ),
            ],
        ];

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Я сохранила /_' . $this->message_id . ' 📝'
            ]
        );
    }

    public function cancel()
    {
        if ('callback_query' != $this->telegram->getUpdateType()) {
            (new Error($this->telegram))->send('Ошибка запроса ⛔', false);
            return;
        }

        $param = get_var_query($this->telegram->Text());

        if (empty($param['message_id'])) {
            (new Error($this->telegram))->send('Я не смогла найти это сообщение 🤷🏻');
            return;
        }

        $this->message_id = $param['message_id'];

        if (!$this->db->existCheckMessage(
            [
                'message_id' => $this->message_id,
                'chat_id' => $this->chat_id,
            ]
        )) {
            (new Error($this->telegram))
                ->send('Сообщение /_' . $this->message_id . ' уже удалено 🙆🏻‍♀️', false);
            return;
        }

        $this->db->deleteMessage(
            [
                'message_id' => $this->message_id,
                'chat_id' => $this->chat_id,
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Я удалила сообщение /_' . $this->message_id . '🙅🏻‍♀️'
            ]
        );
    }
}
