<?php

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testGetVarQuery()
    {
        $this->assertSame(
            ['content_id' => '123'],
            get_var_query('/content/cancel?content_id=123')
        );

        $this->assertSame(
            ['a' => '1', 't' => '2'],
            get_var_query('/content/cancel?a=1&t=2')
        );

        $this->assertSame(
            [],
            get_var_query('/content/cancel')
        );
    }

    public function testShortenLink()
    {
        $url = 'https://www.php.net/manual/ru/function.stristr.php';
        $text = mb_strimwidth('php.net/manual/ru/function.stristr.php', 0, MAX_LINE_LENGTH, "...");

        $this->assertSame(
            '<a href="' . $url . '">' . $text . '</a>',
            shorten_link($url)
        );

        $url = 'http://www.php.net/manual/ru/function.stristr.php';
        $text = mb_strimwidth('php.net/manual/ru/function.stristr.php', 0, MAX_LINE_LENGTH, "...");

        $this->assertSame(
            '<a href="' . $url . '">' . $text . '</a>',
            shorten_link($url)
        );

        $url = 'https://php.net/manual/ru/function.stristr.php';
        $text = mb_strimwidth('php.net/manual/ru/function.stristr.php', 0, MAX_LINE_LENGTH, "...");

        $this->assertSame(
            '<a href="' . $url . '">' . $text . '</a>',
            shorten_link($url)
        );

        $url = 'http://php.net/manual/ru/function.stristr.php';
        $text = mb_strimwidth('php.net/manual/ru/function.stristr.php', 0, MAX_LINE_LENGTH, "...");

        $this->assertSame(
            '<a href="' . $url . '">' . $text . '</a>',
            shorten_link($url)
        );

        $url = 'www.php.net/manual/ru/function.stristr.php';
        $text = mb_strimwidth('php.net/manual/ru/function.stristr.php', 0, MAX_LINE_LENGTH, "...");

        $this->assertSame(
            '<a href="https://' . $url . '">' . $text . '</a>',
            shorten_link($url)
        );

        $text = 'asd3e23e2d23d23e23 3e32e23e2 3e23 e23e 23e3e23 e23e 23e23 e23e 23e 3 ';
        $text = mb_strimwidth($text, 0, MAX_LINE_LENGTH, "...");

        $this->assertSame(
            $text,
            shorten_link($text)
        );

        $url = 'text';

        $this->assertSame(
            'text',
            shorten_link($url)
        );
    }

    public function testIsUrl()
    {
        $this->assertTrue(
            is_url('https://entropy.report/tri-glavnykh')
        );

        $this->assertTrue(
            is_url('www.php.net/manual/ru')
        );

        $this->assertFalse(
            is_url('netwwwphpsdfsdf')
        );

        $this->assertFalse(
            is_url('nehttpsasu')
        );

        $this->assertFalse(
            is_url('https sdsfsdfds')
        );

        $this->assertFalse(
            is_url('Делает ли это тебя счастливым? https://entropy.repor')
        );
    }

    public function testShortenLine()
    {
        $text = 'А вот ты сейчас это делаешь чтобы что?
Приносит ли тебе это пользу?
Делает ли это тебя счастливым?

https://entropy.report/tri-glavnykh-voprosa-i-odin-samyy-glavniy/';
        $ready = mb_strimwidth($text, 0, MAX_LINE_LENGTH, "...");

        $this->assertSame(
            $ready,
            shorten_line($text)
        );
    }
}
