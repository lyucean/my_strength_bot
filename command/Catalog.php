<?php


namespace msb\command;

use msb\core\DB;
use Telegram;

class Catalog
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

    /**
     * method for sending messages
     * @param $message
     */
    protected function send($message)
    {
        if (empty($message)) {
            return;
        }

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => $message,
                'disable_web_page_preview' => true,
                'parse_mode' => 'html'
            ]
        );
    }

    /**
     * forms a list of messages to send
     * @param $messages
     * @return array
     */
    public function preparation($messages)
    {
        $answer = [];

        foreach ($messages as $message) {
            $text = $message['text'];
            $image = $message['image'];

            if (!empty($text)) {
                $text = shorten_line($message['text']);
            }
            if (!empty($image)) {
                $text = ' 🖌️ - ' . $text;
            }
            $answer[] = '/_' . $message['message_id'] . ' - ' . $text . "\n";
        }

        return $answer;
    }

    public function index()
    {
        $messages = $this->db->getMessages($this->chat_id);

        if (empty($messages)) {
            $this->send('Список пока пуст 🙃');
            return;
        }

        $max_message_length = 4000;
        $message = '';

        foreach ($this->preparation($messages) as $row) {
            if ($max_message_length < mb_strlen($message . $row)) {
                $this->send($message);
                $message = '';
            }

            $message .= $row;
        }

        $this->send($message);
    }
}
