<?php

namespace msb\command;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Telegram;

/**
 * Class MessageTest
 * @package msb\command
 * The test must be run entirely, otherwise it will produce extra entries in the database
 */
class MessageTest extends TestCase
{
    private MockObject $mock_telegram;

    public function testAdd()
    {
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn('This is a test entry.');

        $action = new Message($this->mock_telegram);

        $action->add();

        $message_id = $action->__debugInfo()['message_id'];

        $this->assertTrue((bool)$message_id);

        return $message_id;
    }

    /**
     * @depends testAdd
     * @param $message_id
     */
    public function testCancel($message_id)
    {
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn('/message/cancel?message_id=' . $message_id);

        try {
            $action = new Message($this->mock_telegram);

            $action->cancel();

            $this->assertSame((int)$message_id, (int)$action->__debugInfo()['message_id']);
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertTrue(true);
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

        $this->mock_telegram->expects($this->any())
            ->method('MessageID')
            ->willReturn(444);
    }
}
