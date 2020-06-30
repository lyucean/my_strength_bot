<?php

namespace msb\command;

use msb\core\DB;
use Telegram;

class Message
{
    private Telegram $telegram;
    private int $chat_id = 0;
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
        $this->db->editMessageByMessageId(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Editing has been saved.'
            ]
        );
    }

    public function addImage()
    {
//        $this->db->editMessageByMessageId(
//            [
//                'chat_id' => $this->chat_id,
//                'text' => $this->telegram->Text(),
//                'message_id' => $this->telegram->MessageID(),
//            ]
//        );

        // take the highest resolution
        $array = $this->telegram->Photo();
        $file = $this->telegram->getFile(array_pop($array)['file_id']);

        if (!array_key_exists('ok', $file) || !array_key_exists('result', $file)) {
            (new Error($this->telegram))->send('I could not download the picture, the server is unavailable.');
        }

        $file_path = $file['result']['file_path'];
        $file_name = $file['result']['file_unique_id'] . '.jpg';

        $url_on_server = 'https://api.telegram.org/file/bot' . TELEGRAM_TOKEN . '/' . $file_path;

        $folder = rand(10, 999) . '/';

        if (!is_dir(DIR_FILE . $folder)) {
            mkdir(DIR_FILE . $folder);
        }

        file_put_contents(DIR_FILE . $folder . $file_name, file_get_contents($url_on_server));

        $this->message_id = $this->db->addMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Caption(),
                'image' => $folder . $file_name,
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'I saved the image 😉'
            ]
        );
    }

    public function add()
    {
        if (!in_array($this->telegram->getUpdateType(), ['message', 'reply_to_message'])) {
            (new Error($this->telegram))->send('I don\'t know how to work with this type of message.');
            return;
        }

        // double check
        if ($this->db->checkDoubleMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
            ]
        )) {
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => 'This message already exists.'
                ]
            );
            return;
        }

        $this->message_id = $this->db->addMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $this->telegram->Text(),
                'message_id' => $this->telegram->MessageID(),
            ]
        );

        if (!$this->message_id) {
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => 'I could not save this message.'
                ]
            );
            return;
        }

        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Cancel add',
                    $url = '',
                    '/message/cancel?message_id=' . $this->message_id
                ),
            ],
        ];

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'I saved it. №' . $this->message_id
            ]
        );
    }

    public function cancel()
    {
        if ('callback_query' != $this->telegram->getUpdateType()) {
            (new Error($this->telegram))->send('This is not a callback query.', false);
            return;
        }

        $param = get_var_query($this->telegram->Text());

        if (empty($param['message_id'])) {
            (new Error($this->telegram))->send('I did not find message.');
        }

        $this->message_id = $param['message_id'];

        $reply = 'Deleted.';

        if (!$this->db->deleteMessage(
            [
                'message_id' => $this->message_id,
                'chat_id' => $this->chat_id,
            ]
        )) {
            $reply = 'This message has already been removed.';
        }

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $reply
            ]
        );
    }
}
