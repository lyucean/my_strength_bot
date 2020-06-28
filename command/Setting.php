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

        $content = [
            'chat_id' => $this->chat_id,
            'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
            'text' => 'Choose to change'
        ];
        $this->telegram->sendMessage($content);
    }

    public function change_number()
    {
        //Put the command on hold;
        $this->db->setWaitingCommand($this->chat_id, '/setting/set_number');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Enter how many messages to send you per day [max ' . MAXIMUM_OF_MESSAGES_PER_DAY . '].'
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
                'text' => 'Save: ' . $quantity . ' ' . (1 == $quantity ? 'message' : 'messages') . ' every day.'
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
                'text' => 'Enter your time zone offset [number only, eg -3 or +2].'
                    . "\n" . 'If you don’t know what it is, then google "Time zone in your city".'
            ]
        );
    }

    public function set_time_zone()
    {
        $offset = (int)$this->telegram->Text();

        if ($offset < -13 || 14 < $offset) {
            (new Error($this->telegram))->send(
                'I am waiting for a number from -12 to 14',
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
                'text' => 'Save time zone offset: ' . (0 < $offset ? '+' : '') . $offset
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
                'text' => 'Enter the interval at which it is convenient for you to receive messages [eg 9-20 or 10-12].'
            ]
        );
    }

    private function validate_interval($hour_start, $hour_end)
    {
        if ($hour_start < 1 || 24 < $hour_start) {
            $this->error[] = 'I am waiting for a first number from 1 to 24.';
        }
        if ($hour_end < 1 || 24 < $hour_end) {
            $this->error[] = 'I am waiting for a second number from 1 to 24.';
        }
        if ($hour_end < $hour_start) {
            $this->error[] = 'First number cannot be larger than the second.';
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
                'text' => 'Save interval: ' . $hour_start . ':00-' . $hour_end . ':00'
            ]
        );
    }

    public function change_list()
    {
        $option = [
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Show all list',
                    $url = '',
                    '/catalog'
                )
            ],
            [
                $this->telegram->buildInlineKeyBoardButton(
                    'Clear list',
                    $url = '',
                    '/setting/clear_list'
                )
            ],
        ];

        $content = [
            'chat_id' => $this->chat_id,
            'reply_markup' => $this->telegram->buildInlineKeyBoard($option),
            'text' => 'Choose to change'
        ];
        $this->telegram->sendMessage($content);
    }

    public function clear_list()
    {
        $this->db->setWaitingCommand($this->chat_id, '/setting/clear_list_confirm');

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'Are you sure you want to clear the whole list irrevocably?'
                    . "\n" . 'If it is, send a "Yes" in a message.'
            ]
        );
    }

    public function clear_list_confirm()
    {
        $confirm = $this->telegram->Text();

        if (trim(strtolower($confirm)) != "yes") {
            $this->telegram->sendMessage(
                [
                    'chat_id' => $this->chat_id,
                    'text' => 'The list has not been cleared.'
                ]
            );
            return;
        }

        $this->db->clearAllContent($this->chat_id);

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => 'The list has been cleared.'
            ]
        );
    }
}
