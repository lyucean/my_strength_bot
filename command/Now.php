<?php


namespace msb\command;

use msb\core\DB;
use Telegram;

class Now
{
    private Telegram $telegram;
    /**
     * @var mixed
     */
    private int $chat_id;
    /**
     * @var DB
     */
    private DB $db;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function index()
    {
        $message = $this->db->getMessagePrepared($this->chat_id);

        if (empty($message)) { // If there is nothing to send
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => 'Ваш список сообщений пуст.' . "\n" . 'У меня нет ничего для отправки 🤷🏻'
                ]
            );
            return;
        }
        $this->send($message);
    }

    public function get($message_id)
    {
        if (empty($message_id)) {
            (new Error($this->telegram))->send('Я не нашла это сообщение 🕵🏻‍♀️');
        }

        $message = $this->db->getMessage($message_id);

        if (empty($message)) { // If there is nothing to send
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => "Такое сообщение не найдено 🕵🏻‍♀️"
                ]
            );
            return;
        }

        $this->send($message);
    }

    protected function send($message)
    {
        $answer = '';
        // Show marker, if display is off
        if (!$message['display']) {
            $answer .= '[ удалённое сообщение ]' . PHP_EOL;
        }

        $answer .= $message['text'] . PHP_EOL;
        $answer .= ' /_' . $message['message_id'];


        if (!empty($message['image'])) {
            $img = curl_file_create(DIR_FILE . $message['image'], 'image/jpeg');
            $this->telegram->sendPhoto(
                [
                    'chat_id' => $this->chat_id,
                    'photo' => $img,
                    'caption' => $answer
                ]
            );
            return;
        }

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $answer
            ]
        );
    }
}
