<?php

namespace msb\core;

use Exception;
use MysqliDb;

class DB
{
    private MysqliDb $db;

    public function __construct()
    {
        $this->db = new MysqliDb(
            array(
                'host' => DB_HOST,
                'username' => DB_USERNAME,
                'password' => DB_PASSWORD,
                'db' => DB_NAME,
                'port' => DB_PORT,
                'prefix' => '',
                'charset' => 'utf8'
            )
        );

        return $this;
    }

    // SendingDaily ---------------------------------------------------
    public function getSendingDailyNow()
    {
        $this->db->where("date_time", gmdate('Y-m-d H:i:s'), "<=");
        $this->db->where("status_sent", 0);
        return $this->db->get("schedule_daily");
    }

    public function addSendingDailyNow($data)
    {
        return $this->db->insert('schedule_daily', $data);
    }

    public function setScheduleDailyStatusSent($schedule_daily_id)
    {
        $this->db->where('schedule_daily_id', $schedule_daily_id);
        $this->db->update('schedule_daily', ['status_sent' => 1]);
    }

    // Schedule ---------------------------------------------------
    public function getSchedules()
    {
        return $this->db->get("schedule");
    }

    public function getSchedule($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        return $this->db->getOne("schedule");
    }

    public function setSchedule($chat_id, $data)
    {
        if (!empty($data['quantity'])) {
            $change['quantity'] = (int)$data['quantity'];
        }

        if (!empty($data['time_zone_offset'])) {
            $change['time_zone_offset'] = (int)$data['time_zone_offset'];
        }

        if (!empty($data['hour_start'])) {
            $change['hour_start'] = (int)$data['hour_start'];
        }

        if (!empty($data['hour_end'])) {
            $change['hour_end'] = (int)$data['hour_end'];
        }

        if (empty($change)) {
            return;
        }

        $change['chat_id'] = $chat_id;
        $change['date_modified'] = $this->db->now();

        $this->db->replace('schedule', $change);
    }

    public function addSchedule($data)
    {
        $data['date_modified'] = $this->db->now();
        return $this->db->replace('schedule', $data);
    }

    // Message ----------------------------------------------------

    /**
     * Selects which message to send.
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getMessagePrepared($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "asc");
        $message = $this->db->getOne("message");

        if (empty($message)) {
            return [];
        }

        // Add the information that we have already shown this message
        $this->addDateReminderMessage($message['message_id']);

        return $message;
    }

    /**
     * @param $data
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function existCheckMessage($data)
    {
        if (isset($data['text'])) {
            $this->db->where("text", $this->db->escape(trim($data['text'])));
        }
        if (isset($data['message_id'])) {
            $this->db->where("message_id", (int)$data['message_id']);
        }
        $this->db->where("chat_id", $data['chat_id']);
        $this->db->where("display", 1);
        return !empty($this->db->get("message"));
    }

    /**
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getMessages($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "desc");
        return $this->db->get("message");
    }

    /**
     * @param $message_id
     * @return array
     * @throws Exception
     */
    public function getMessage($message_id)
    {
        $this->db->where("message_id", (int)$message_id);
        return $this->db->getOne("message");
    }

    /**
     * @param int $chat_id
     * @return array
     * @throws Exception
     */
    public function getLastMessage($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->orderBy("date_reminder", "DESC");
        $this->db->where("display", 1);

        return $this->db->getOne("message");
    }

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function deleteMessage($data)
    {
        $this->db->where('message_id', $data['message_id']);
        $this->db->where('chat_id', $data['chat_id']);
        return $this->db->delete('message');
    }

    /**
     * @param $chat_id
     * @return bool
     * @throws Exception
     */
    public function clearAllMessage($chat_id)
    {
        $this->db->where('chat_id', $chat_id);
        return $this->db->update(
            'message',
            [
                'display' => 0
            ]
        );
    }

    /**
     * Adds message, returns message_id
     * @param $data
     * @throws Exception
     */
    public function addMessage($data)
    {
        $this->db->insert(
            'message',
            [
                'message_id' => $data['message_id'],
                'chat_id' => $data['chat_id'],
                'text' => $this->db->escape(trim($data['text'])),
                'image' => $data['image'] ?? '',
                'view' => 0,
                'date_added' => $this->db->now(),
                'date_reminder' => $this->db->now(),
                'display' => 1,
            ]
        );
    }

    /**
     * update date reminder and view for Message
     * @param $message_id
     * @throws Exception
     */
    public function addDateReminderMessage($message_id)
    {
        $this->db->where('message_id', $message_id);
        $this->db->update(
            'message',
            [
                'date_reminder' => $this->db->now(),
                'view' => $this->db->inc()
            ]
        );
    }

    /**
     * update Message
     * @param $data
     * @throws Exception
     */
    public function editMessageByMessageId($data)
    {
        $this->db->where('message_id', $data['message_id']);
        $this->db->where('chat_id', $data['chat_id']);

        if (isset($data['text'])) {
            $changes['text'] = $this->db->escape(trim($data['text']));
        }
        if (isset($data['display'])) {
            $changes['display'] = (bool)$data['display'];
        }

        if (isset($changes)) {
            $this->db->update(
                'message',
                $changes
            );
        }
    }

    // ChatHistory ------------------------------------------------
    public function addChatHistory($data)
    {
        $data['date_added'] = $this->db->now();
        $this->db->insert('chat_history', $data);
    }

    // WaitingCommand ---------------------------------------------
    public function getWaitingCommand($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        return $this->db->getOne("command_waiting", 'command');
    }

    public function cleanWaitingCommand($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        return $this->db->delete('command_waiting');
    }

    public function setWaitingCommand($chat_id, $command)
    {
        $this->db->replace(
            'command_waiting',
            [
                'chat_id' => $chat_id,
                'date_added' => $this->db->now(),
                'command' => $this->db->escape($command)
            ]
        );
    }
}
