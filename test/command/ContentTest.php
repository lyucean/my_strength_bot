<?php

namespace msb\command;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Telegram;

/**
 * Class ContentTest
 * @package msb\command
 * The test must be run entirely, otherwise it will produce extra entries in the database
 */
class ContentTest extends TestCase
{
    private MockObject $mock_telegram;

    public function testAdd()
    {
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn('This is a test entry.');

        $action = new Content($this->mock_telegram);

        $action->add();

        $content_id = $action->__debugInfo()['content_id'];

        $this->assertTrue((bool)$content_id);

        return $content_id;
    }

    /**
     * @depends testAdd
     * @param $content_id
     */
    public function testCancel($content_id)
    {
        $this->mock_telegram->expects($this->any())
            ->method('Text')
            ->willReturn('/content/cancel?content_id=' . $content_id);

        try {
            $action = new Content($this->mock_telegram);

            $action->cancel();

            $this->assertSame((int)$content_id, (int)$action->__debugInfo()['content_id']);
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
            ->willReturn(TELEGRAM_TEST_CHAT_ID);

        $this->mock_telegram->expects($this->any())
            ->method('MessageID')
            ->willReturn(444);
    }
}
