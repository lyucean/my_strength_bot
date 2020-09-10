<?php

namespace msb\command;

use Exception;
use PHPUnit\Framework\TestCase;
use Telegram;

class StartTest extends TestCase
{

    public function testIndex()
    {
        try {
            // Create a stub for a class Telegram.
            $Telegram = $this->getMockBuilder(Telegram::class)
                ->onlyMethods(['ChatID'])
                ->getMock();

            $Telegram->expects($this->once())
                ->method('ChatID')
                ->willReturn($_ENV['TELEGRAM_TEST_CHAT_ID']);

            $action = new Start($Telegram);

            $action->index();
        } catch (Exception $e) {
            $this->fail($e);
        }

        $this->assertTrue(true);
    }
}
