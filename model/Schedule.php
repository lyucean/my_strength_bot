<?php

namespace msb\model;

use DateTime;
use Exception;
use msb\core\Model;

class Schedule extends Model
{

    /**
     * Checking to see if it's time for an alert, if it is, it sends it out
     * @throws Exception
     */
    public function check()
    {
        foreach ($this->db->getSendingDailyNow() as $item) {
            $message = $this->db->getMessagePrepared($item['chat_id']);

            if (empty($message)) {
                continue;
            }

            $answer = $message['text'] . ' /_' . $message['message_id'];

            // if this is image
            if (!empty($message['image'])) {
                $img = curl_file_create(DIR_FILE . $message['image'], 'image/jpeg');
                $this->telegram->sendPhoto(
                    [
                        'chat_id' => $item['chat_id'],
                        'photo' => $img,
                        'caption' => $answer
                    ]
                );
                return;
            }

            // default is text
            $this->telegram->sendMessage(
                [
                    'chat_id' => $item['chat_id'],
                    'text' => $answer
                ]
            );

            $this->db->setScheduleDailyStatusSent($item['schedule_daily_id']);
        }
    }

    /**
     * Generates the date and time of the alert in mysql format with an offset from the time zone
     * @param int $hour_start
     * @param int $hour_end
     * @param int $time_zone_offset
     * @return string
     * @throws Exception
     */
    public function createDateTimeForSchedule(int $hour_start, int $hour_end, int $time_zone_offset): string
    {
        $date_starting = gmdate('Y-m-d ' . rand($hour_start, $hour_end) . ':' . rand(10, 59) . ':s');
        $date = new DateTime($date_starting);
        $date->modify('+' . (-1) * $time_zone_offset . ' hours');
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Fills out the schedule of notifications for the current day
     */
    public function generate()
    {
        // every day we will create a schedule for today
        if ('0' != gmdate("G") || '01' != gmdate("i")) {
            return;
        }

        foreach ($this->db->getSchedules() as $item) {
            // how many notifications to send per day
            for ($i = 0; $i < $item['quantity']; $i++) {
                $this->db->addSendingDailyNow(
                    [
                        'chat_id' => $item['chat_id'],
                        'date_time' => $this->createDateTimeForSchedule(
                            $item['hour_start'],
                            $item['hour_end'],
                            $item['time_zone_offset']
                        ),
                        'status_sent' => 0,
                    ]
                );
            }
        }
    }
}
