<?php


namespace msb\command;


use Exception;
use msb\core\DB;
use Telegram;

class Change
{
    private Telegram $telegram;
    private int $chat_id;
    private DB $db;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function index()
    {
        // available only if messages exist
        if (empty($this->db->getMessages($this->chat_id))) {
            (new Error($this->telegram))->send(
                'У вас пока нет сообщений и мне нечего менять',
                false
            );
            return;
        }

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard(
                    [
                        [
                            $this->telegram->buildInlineKeyBoardButton(
                                '✏ Изменить последнее присланное ',
                                $url = '',
                                '/change/choice_last_sent'
                            )
                        ],
                        [
                            $this->telegram->buildInlineKeyBoardButton(
                                '✏️Изменить по номеру',
                                $url = '',
                                '/change/choice_choice'
                            )
                        ],
                        [
                            $this->telegram->buildInlineKeyBoardButton(
                                '❌ Удалить последнее присланное',
                                $url = '',
                                '/change/delete_last_sent'
                            )
                        ],
                        [
                            $this->telegram->buildInlineKeyBoardButton(
                                '❌ Удалить по номеру',
                                $url = '',
                                '/change/delete_choice'
                            )
                        ],
                    ]
                ),
                'text' => 'Что мне сделать?'
            ]
        );
    }

    public function delete_choice()
    {
        //Put the command on hold;
        $this->db->setWaitingCommand($this->chat_id, '/change/delete');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Введи номер сообщения, который нужно изменить [я жду просто цифру например 10].'
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        $message_id = $this->telegram->Text();

        if (!is_numeric($message_id)) {
            (new Error($this->telegram))->send('Я ожидаю число и оно должно быть больше 0', false);
            // return the command on hold;
            $this->db->setWaitingCommand($this->chat_id, '/change/delete');
            return;
        }

        if (!$this->db->ExistCheckMessage(
            [
                'message_id' => $message_id,
                'chat_id' => $this->chat_id,
            ]
        )) {
            (new Error($this->telegram))->send(
                'Сообщение /_' . $message_id . ' уже удалено или не существует 🤚',
                false
            );
            return;
        }

        $this->db->deleteMessage(
            [
                'message_id' => $message_id,
                'chat_id' => $this->chat_id,
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Я удалила сообщение /_' . $message_id . ' 👌'
            ]
        );
    }

    public function delete_last_sent()
    {
        $m_last = $this->db->getLastMessage($this->chat_id);

        if (empty($m_last)) {
            (new Error($this->telegram))->send('Нет сообщений 🤚', false);
        }

        $this->db->deleteMessage(
            [
                'message_id' => $m_last['message_id'],
                'chat_id' => $this->chat_id,
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Сообщение /_' . $m_last['message_id']
                    . ' "' . shorten_line($m_last['text']) . '" удалено 👌'
            ]
        );
    }
}
