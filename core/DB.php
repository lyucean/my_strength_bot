<?php

namespace msb\core;

use Exception;
use MysqliDb;

class DB
{
    private MysqliDb $db;

    public function __construct()
    {
        $this->db = new MysqliDb (
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

    // Content ----------------------------------------------------

    /**
     * Selects which content to send.
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getContentPrepared($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "asc");
        $content = $this->db->getOne("content");

        if (empty($content)) {
            return [];
        }

        // Add the information that we have already shown this content
        $this->addDateReminderContent($content['content_id']);

        return $content;
    }

    /**
     * @param $data
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function checkDoubleContent($data)
    {
        $this->db->where("text", $this->db->escape($data['text']));
        $this->db->where("chat_id", $data['chat_id']);
        $this->db->where("display", 1);
        return !empty($this->db->get("content"));
    }

    /**
     * @param $chat_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getContents($chat_id)
    {
        $this->db->where("chat_id", $chat_id);
        $this->db->where("display", 1);
        $this->db->orderBy("date_reminder", "desc");
        return $this->db->get("content");
    }

    /**
     * @param $content_id
     * @return array|MysqliDb|string
     * @throws Exception
     */
    public function getContent($content_id)
    {
        $this->db->where("content_id", (int)$content_id);
        $this->db->where("display", 1);

        return $this->db->getOne("content");
    }

    /**
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function deleteContent($data)
    {
        $this->db->where('content_id', $data['content_id']);
        $this->db->where('chat_id', $data['chat_id']);
        return $this->db->delete('content');
    }

    /**
     * @param $chat_id
     * @return bool
     * @throws Exception
     */
    public function clearAllContent($chat_id)
    {
        $this->db->where('chat_id', $chat_id);
        return $this->db->update(
            'content',
            [
                'display' => 0
            ]
        );
    }

    /**
     * Adds content, returns content_id
     * @param $data
     * @return int content_id
     * @throws Exception
     */
    public function addContent($data)
    {
        return $this->db->insert(
            'content',
            [
                'chat_id' => $data['chat_id'],
                'text' => $this->db->escape($data['text']),
                'image' => $data['image'] ?? '',
                'message_id' => $data['message_id'],
                'rating' => 0,
                'date_added' => $this->db->now(),
                'date_reminder' => $this->db->now(),
                'display' => 1,
            ]
        );
    }

    /**
     * update date reminder and rating for Content
     * @param $content_id
     * @throws Exception
     */
    public function addDateReminderContent($content_id)
    {
        $this->db->where('content_id', $content_id);
        $this->db->update(
            'content',
            [
                'date_reminder' => $this->db->now(),
                'rating' => $this->db->inc()
            ]
        );
    }

    /**
     * update Content
     * @param $data
     * @throws Exception
     */
    public function editContentByMessageId($data)
    {
        $this->db->where('message_id', $data['message_id']);
        $this->db->where('chat_id', $data['chat_id']);
        $this->db->update(
            'content',
            [
                'text' => $this->db->escape($data['text'])
            ]
        );
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
