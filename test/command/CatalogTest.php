<?php

namespace msb\command;

use PHPUnit\Framework\TestCase;
use msb\core\DB;
use Telegram;

class CatalogTest extends TestCase
{
    private DB $db;
    private Telegram $mock_telegram;

    public function testPreparation()
    {
        $contents = $this->db->getContents(TELEGRAM_TEST_CHAT_ID);

        $catalog = new Catalog($this->mock_telegram);
        $catalog->preparation($contents);
    }

    public function testIndex()
    {
    }

    protected function setUp(): void
    {
        $this->db = new DB();

        // Create a stub for a class Telegram.
        $this->mock_telegram = $this->getMockBuilder(Telegram::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ChatID', 'MessageID', 'Text'])
            ->getMock();

        $this->mock_telegram->expects($this->any())
            ->method('ChatID')
            ->willReturn(TELEGRAM_TEST_CHAT_ID);
    }
}
