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
        $content = $this->db->getContentPrepared($this->chat_id);

        if (empty($content)) { // If there is nothing to send
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => "Your message list is empty.\nI have nothing to send you."
                ]
            );
            return;
        }
        $this->send($content);
    }

    public function get($content_id)
    {
        if (empty($content_id)) {
            (new Error($this->telegram))->send('I did not find message.');
        }

        $content = $this->db->getContent($content_id);

        if (empty($content)) { // If there is nothing to send
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => "No such message found."
                ]
            );
            return;
        }
        $this->send($content);
    }

    protected function send($content)
    {
        $message = $content['text'] . ' №' . $content['content_id'];

        if (!empty($content['image'])) {
            $img = curl_file_create(DIR_FILE . $content['image'], 'image/jpeg');
            $this->telegram->sendPhoto(
                [
                    'chat_id' => $this->chat_id,
                    'photo' => $img,
                    'caption' => $message
                ]
            );
            return;
        }

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,

//                'reply_markup' => $this->telegram->buildInlineKeyBoard(
//                    [
//                        [
//                            $this->telegram->buildInlineKeyBoardButton(
//                                'Delete this',
//                                $url = '',
//                                '/content/cancel?content_id=' . $content['content_id']
//                            ),
//                        ],
//                    ]
//                ),
                'text' => $message
            ]
        );
    }
}
