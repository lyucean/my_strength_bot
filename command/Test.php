<?php


namespace msb\command;


use Telegram;

class Test
{
    private Telegram $telegram;
    private int $chat_id;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
    }

    public function index()
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => '
*bold \*text*
_italic \*text_
__underline__
~strikethrough~
*bold _italic bold ~italic bold strikethrough~ __underline italic bold___ bold*
[inline URL](http://www.example.com/)
[inline mention of a user](tg://user?id=123456789)
`inline fixed-width code`
```
pre-formatted fixed-width code block
```
```python
pre-formatted fixed-width code block written in the Python programming language
```
                ',
                'parse_mode' => 'MarkdownV2'
            ]
        );
    }
}
