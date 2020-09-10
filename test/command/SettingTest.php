<?php

namespace msb\command;

use Exception;
use msb\core\DB;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Telegram;

class SettingTest extends TestCase
{
    private MockObject $mock_telegram;
    private DB $db;

    public function testIndex()
    {
        try {
            $action = new Setting($this->mock_telegram);

            $action->index();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertTrue(true);
    }

    public function testChange_number()
    {
        try {
            $action = new Setting($this->mock_telegram);

            $action->change_number();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertTrue(true);
    }

    public function testSet_number()
    {
        $number = rand(1, $_ENV['MAX_OF_MESSAGES_PER_DAY']);
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn($number);

        try {
            $action = new Setting($this->mock_telegram);
            $action->set_number();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $schedule = $this->db->getSchedule($_ENV['TELEGRAM_TEST_CHAT_ID']);
        $this->assertSame($number, $schedule['quantity']);

        $this->db->setSchedule($_ENV['TELEGRAM_TEST_CHAT_ID'], ['quantity' => 1]);
    }

    public function testChange_time_zone()
    {
        try {
            $action = new Setting($this->mock_telegram);

            $action->change_time_zone();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertTrue(true);
    }

    public function testSet_time_zone()
    {
        $offset = rand(-12, +14);

        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn($offset);

        try {
            $action = new Setting($this->mock_telegram);
            $action->set_time_zone();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $schedule = $this->db->getSchedule($_ENV['TELEGRAM_TEST_CHAT_ID']);
        $this->assertSame($offset, $schedule['time_zone_offset']);

        $this->db->setSchedule($_ENV['TELEGRAM_TEST_CHAT_ID'], ['time_zone_offset' => 3]);
    }

    public function testChange_interval()
    {
        try {
            $action = new Setting($this->mock_telegram);

            $action->change_interval();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertTrue(true);
    }

    public function testSet_intervalFailure_1()
    {
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn('32-15');

        $action = new Setting($this->mock_telegram);
        $this->expectException(Exception::class);
        $action->set_interval();
    }

    public function testSet_intervalFailure_3()
    {
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn('15-32');

        $action = new Setting($this->mock_telegram);
        $this->expectException(Exception::class);
        $action->set_interval();
    }

    public function testSet_intervalFailure_2()
    {
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn('15-10');

        $action = new Setting($this->mock_telegram);
        $this->expectException(Exception::class);
        $action->set_interval();
    }

    public function testSet_interval()
    {
        $hour_start = 10;
        $hour_end = 13;

        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn($hour_start . '-' . $hour_end);

        try {
            $action = new Setting($this->mock_telegram);
            $action->set_interval();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $schedule = $this->db->getSchedule($_ENV['TELEGRAM_TEST_CHAT_ID']);
        $this->assertSame($hour_start, $schedule['hour_start']);
        $this->assertSame($hour_end, $schedule['hour_end']);

        $this->db->setSchedule(
            $_ENV['TELEGRAM_TEST_CHAT_ID'],
            [
                'hour_start' => 9,
                'hour_end' => 13,
            ]
        );
    }

    protected function setUp(): void
    {
        // Create a stub for a class Telegram.
        $this->mock_telegram = $this->getMockBuilder(Telegram::class)
            ->onlyMethods(['ChatID', 'MessageID', 'Text'])
            ->getMock();

        $this->mock_telegram->expects($this->any())
            ->method('ChatID')
            ->willReturn($_ENV['TELEGRAM_TEST_CHAT_ID']);

        $this->db = new DB();
    }
}
