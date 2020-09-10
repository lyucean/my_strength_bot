<?php

namespace msb\command;

use Exception;
use msb\core\DB;
use PHPUnit\Framework\TestCase;
use Telegram;

class CatalogTest extends TestCase
{

    public function testPreparation()
    {
        $db = new DB();

        // Create a stub for a class Telegram.
        $mock_telegram = $this->getMockBuilder(Telegram::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ChatID', 'MessageID', 'Text'])
            ->getMock();

        $mock_telegram->expects($this->any())
            ->method('ChatID')
            ->willReturn($_ENV['TELEGRAM_TEST_CHAT_ID']);

        try {
            $messages = $db->getMessages($_ENV['TELEGRAM_TEST_CHAT_ID']);

            $catalog = new Catalog($mock_telegram);
            $catalog->preparation($messages);
        } catch (Exception $e) {
            $this->fail();
        }
    }
}
