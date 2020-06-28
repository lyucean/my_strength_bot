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
     * @param $contents
     * @return array
     */
    public function preparation($contents)
    {
        $message = [];

        foreach ($contents as $content) {
            $text = $content['text'];
            $image = $content['image'];

            if (!empty($text)) {
                $text = shorten_line($content['text']);
            }
            if (!empty($image)) {
                $text = ' This is a picture: ' . '/get_' . $content['content_id'];
            }
            $text = "<b>№" . $content['content_id'] . '</b> - ' . $text . "\n";
            $message[] = $text;
        }

        return $message;
    }

    public function index()
    {
        $contents = $this->db->getContents($this->chat_id);

        if (empty($contents)) {
            $this->send('Your list is empty.');
            return;
        }

        $max_message_length = 4000;
        $message = '';

        foreach ($this->preparation($contents) as $row) {
            if ($max_message_length < mb_strlen($message . $row)) {
                $this->send($message);
                $message = '';
            }

            $message .= $row;
        }

        $this->send($message);
    }

}
