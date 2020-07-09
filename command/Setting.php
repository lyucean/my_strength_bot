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
                    '⏰ Интервал сообщений: от' . $hour_start . ':00 до ' . $hour_end . ':00',
                    $url = '',
                    '/setting/change_interval'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    '🎚 Количество сообщений: ' . $quantity . ' ' . 'шт',
                    $url = '',
                    '/setting/change_number'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    '🕧 Ваша временная зона: ' . ($time_zone_offset < 0 ? '' : '+') . $time_zone_offset,
                    $url = '',
                    '/setting/change_time_zone'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    '❌ Очистить все сохранённые сообщения',
                    $url = '',
                    '/setting/clear_list'
                )
            ]
        ];

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
                'text' => 'Что будем настраивать? 👩🏻‍🔧'
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
                'text' => 'Введи количество сообщений, которое мне отправлять каждый день [максимум ' . MAXIMUM_OF_MESSAGES_PER_DAY . '].'
            ]
        );
    }

    public function set_number()
    {
        $quantity = (int)$this->telegram->Text();

        if ($quantity < 1 || MAXIMUM_OF_MESSAGES_PER_DAY < $quantity) {
            (new Error($this->telegram))->send(
                'Я ожидаю цифру от 1 до ' . MAXIMUM_OF_MESSAGES_PER_DAY,
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
                'text' => 'Сохранила: ' . $quantity . ' ' . (1 == $quantity ? 'сообщение' : 'сообщений') . ' каждый день.'
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
                'text' => 'Введи часовой пояс [только число со знаком, например, -3 или +2].'
                    . "\n" . 'Если не знаешь, что это такое, поищи в Google "Часовой пояс в вашем городе".'
            ]
        );
    }

    public function set_time_zone()
    {
        $offset = (int)$this->telegram->Text();

        if ($offset < -13 || 14 < $offset) {
            (new Error($this->telegram))->send(
                'Я жду число от -12 до 14',
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
                'text' => 'Сохранила временную зону:' . (0 < $offset ? '+' : '') . $offset
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
                'text' => 'Пришли интервал, в котором мне отправлять тебе сообщения [например, 9-20 или 10-12].'
            ]
        );
    }

    private function validate_interval($hour_start, $hour_end)
    {
        if ($hour_start < 1 || 24 < $hour_start) {
            $this->error[] = 'Я ожидала первое число от 1 до 24.';
        }
        if ($hour_end < 1 || 24 < $hour_end) {
            $this->error[] = 'Я ожидала второе число от 1 до 24.';
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
                'text' => 'Сохранила интервал: ' . $hour_start . ':00-' . $hour_end . ':00'
            ]
        );
    }

    public function clear_list()
    {
        $this->db->setWaitingCommand($this->chat_id, '/setting/clear_list_confirm');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Точно хочешь удалить все сообщения безвозвратно?'
                    . "\n" . 'Если да, отправь «Да» в сообщении.'
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
                    'text' => 'Сообщения не удалены 🙅🏻‍♀️ т.к. я ждала «Да» в сообщении'
                ]
            );
            return;
        }

        $this->db->clearAllMessage($this->chat_id);

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Я удалила все сообщения 👷🏻‍♀️'
            ]
        );
    }
}
