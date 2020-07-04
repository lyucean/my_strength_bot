<?php


namespace msb\command;

use msb\core\DB;
use Telegram;

class Setting
{
    private Telegram $telegram;
    private int $chat_id;
    private DB $db;
    private array $error = [];

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
        $this->db = new DB();
    }

    public function index()
    {
        $schedule = $this->db->getSchedule($this->chat_id);

        $hour_start = $schedule['hour_start'] ?? 9;
        $hour_end = $schedule['hour_end'] ?? 14;
        $quantity = $schedule['quantity'] ?? 1;
        $time_zone_offset = $schedule['time_zone_offset'] ?? 3;

        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Message Interval: from ' . $hour_start . ':00 to ' . $hour_end . ':00',
                    $url = '',
                    '/setting/change_interval'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Number of messages: ' . $quantity . ' ' . (1 == $quantity ? 'message' : 'messages'),
                    $url = '',
                    '/setting/change_number'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Your time zone: ' . ($time_zone_offset < 0 ? '' : '+') . $time_zone_offset,
                    $url = '',
                    '/setting/change_time_zone'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Your list',
                    $url = '',
                    '/setting/change_list'
                )
            ],
        ];

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Выберите для изменения:'
            ]
        );
    }

    public function change_number()
    {
        //Put the command on hold;
        $this->db->setWaitingCommand($this->chat_id, '/setting/set_number');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Введите количество сообщений, которое отправлять вам каждый день [максимум ' . MAXIMUM_OF_MESSAGES_PER_DAY . '].'
            ]
        );
    }

    public function set_number()
    {
        $quantity = (int)$this->telegram->Text();

        if ($quantity < 1 || MAXIMUM_OF_MESSAGES_PER_DAY < $quantity) {
            (new Error($this->telegram))->send(
                'I am waiting for a number from 1 to ' . MAXIMUM_OF_MESSAGES_PER_DAY,
                false
            );
            // return the command on hold;
            $this->db->setWaitingCommand($this->chat_id, '/setting/set_number');
            return;
        }

        $this->db->setSchedule($this->chat_id, ['quantity' => $quantity]);

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Сохранил: ' . $quantity . ' ' . (1 == $quantity ? 'сообщение' : 'сообщений') . ' каждый день.'
            ]
        );
    }

    public function change_time_zone()
    {
        //Put the command on hold;
        $this->db->setWaitingCommand($this->chat_id, '/setting/set_time_zone');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Введите смещение часового пояса [только число со знаком, например, -3 или +2].'
                    . "\n" . 'Если вы не знаете, что это такое, воспользуйтесь Google "Часовой пояс в вашем городе".'
            ]
        );
    }

    public function set_time_zone()
    {
        $offset = (int)$this->telegram->Text();

        if ($offset < -13 || 14 < $offset) {
            (new Error($this->telegram))->send(
                'Жду число от -12 до 14',
                false
            );
            // return the command on hold;
            $this->db->setWaitingCommand($this->chat_id, '/setting/set_number');
            return;
        }

        $this->db->setSchedule($this->chat_id, ['time_zone_offset' => $offset]);

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Сохранил временную зону:' . (0 < $offset ? '+' : '') . $offset
            ]
        );
    }

    public function change_interval()
    {
        //Put the command on hold;
        $this->db->setWaitingCommand($this->chat_id, '/setting/set_interval');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Введите интервал, в котором вам удобно получать сообщения [например, 9-20 или 10-12].'
            ]
        );
    }

    private function validate_interval($hour_start, $hour_end)
    {
        if ($hour_start < 1 || 24 < $hour_start) {
            $this->error[] = 'Я ожидаю первое число от 1 до 24.';
        }
        if ($hour_end < 1 || 24 < $hour_end) {
            $this->error[] = 'Я ожидаю второе число от 1 до 24.';
        }
        if ($hour_end < $hour_start) {
            $this->error[] = 'Второе число не может быть больше первого.';
        }

        return count($this->error) == 0;
    }

    public function set_interval()
    {
        $interval = $this->telegram->Text();

        $hour_start = (int)stristr($interval, '-', true);
        $hour_end = (int)ltrim(stristr($interval, '-'), " -");

        if (!$this->validate_interval($hour_start, $hour_end)) {
            (new Error($this->telegram))->send(implode("\n", $this->error), false);
            // return the command on hold;
            $this->db->setWaitingCommand($this->chat_id, '/setting/set_interval');
            return;
        }

        $this->db->setSchedule(
            $this->chat_id,
            [
                'hour_start' => $hour_start,
                'hour_end' => $hour_end,
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Сохранил интервал: ' . $hour_start . ':00-' . $hour_end . ':00'
            ]
        );
    }

    public function change_list()
    {
        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Показать все сохранённые сообщения',
                    $url = '',
                    '/catalog'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Очистить все сохранённые сообщения',
                    $url = '',
                    '/setting/clear_list'
                )
            ],
        ];

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Выберите, чтобы изменить:'
            ]
        );
    }

    public function clear_list()
    {
        $this->db->setWaitingCommand($this->chat_id, '/setting/clear_list_confirm');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Вы уверены, что хотите удалить все сообщения безвозвратно?'
                    . "\n" . 'Если это так, отправьте «Да» в сообщении.'
            ]
        );
    }

    public function clear_list_confirm()
    {
        $confirm = $this->telegram->Text();

        if (trim(strtolower($confirm)) != "да") {
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => 'Сообщения не были удалены, т.к. я ждал «Да» в сообщении.'
                ]
            );
            return;
        }

        $this->db->clearAllMessage($this->chat_id);

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Все сообщения были удалены.'
            ]
        );
    }
}
