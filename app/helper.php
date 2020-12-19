<?php

// debug display function
if (!function_exists('ddf')) {
    /**
     * @param $var
     * @param bool $die
     */
    function ddf($var, $die = true)
    {
        echo PHP_EOL . gmdate("i:s") . ' ' . PHP_EOL;
        print_r($var);
        flush();
        if ($die) {
            die;
        }
    }
}

// get query param
if (!function_exists('get_var_query')) {
    /**
     * @param string $string
     * @return array
     */
    function get_var_query(string $string)
    {
        $string = parse_url($string);

        if (empty($string['query'])) {
            return [];
        }

        parse_str($string['query'], $query);

        return $query;
    }
}

// shorten_line
if (!function_exists('shorten_line')) {
    /**
     * @param string $text
     * @return string|string[]|null
     */
    function shorten_line(string $text)
    {
        if (is_url($text)) {
            return shorten_link($text);
        }

        return shorten_text($text, $_ENV['MAX_LINE_LENGTH']);
    }
}

// is_url
if (!function_exists('is_url')) {
    /**
     * @param string $text
     * @return bool
     */
    function is_url(string $text)
    {
        return (bool)preg_match('~^(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![.,:])~i', $text);
    }
}

// shorten_text
if (!function_exists('shorten_text')) {
    /**
     * @param string $text
     * @param int $max_line_length
     * @return string|string[]
     */
    function shorten_text(string $text, int $max_line_length = 10)
    {
        // cut www.
        $text = str_replace("www.", "", $text);

        // cut ...
        $text = str_replace("...", "", $text);

        // cut to length
        if ($max_line_length < iconv_strlen($text, 'UTF-8')) {
            $text = mb_strimwidth($text, 0, $max_line_length, "...");
        }

        return $text;
    }
}

// fixes line breaks
if (!function_exists('fix_breaks')) {
    /**
     * @param string $text
     * @return string
     */
    function fix_breaks(string $text) : string
    {
        return str_replace( '\n', "\n", $text );
    }
}

// shorten_link
if (!function_exists('shorten_link')) {
    /**
     * @param $value
     * @param string[] $protocols
     * @return string|string[]|null
     */
    function shorten_link($value, $protocols = array('https', 'http', 'mail'))
    {
        $links = array();

        // Extract existing links and tags
        $value = preg_replace_callback(
            '~(<a .*?>.*?</a>|<.*?>)~i',
            function ($match) use (&$links) {
                return '<' . array_push($links, $match[1]) . '>';
            },
            $value
        );

        // Extract text links for each protocol
        foreach ((array)$protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':
                    $value = preg_replace_callback(
                        '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![.,:])~i',
                        function ($match) use ($protocol, &$links) {
                            if ($match[1]) {
                                $protocol = $match[1];
                            }
                            $link = $match[2] ?: $match[3];
                            return '<' . array_push(
                                $links,
                                "<a href=\"$protocol://$link\">" . shorten_text($link, $_ENV['MAX_LINE_LENGTH']) . "</a>"
                            ) . '>';
                        },
                        $value
                    );
                    break;
                case 'mail':
                    $value = preg_replace_callback(
                        '~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![.,:])~',
                        function ($match) use (&$links) {
                            return '<' . array_push(
                                $links,
                                "<a href=\"mailto:{$match[1]}\">" . shorten_text(
                                    $match[1]
                                , $_ENV['MAX_LINE_LENGTH']) . "</a>"
                            ) . '>';
                        },
                        $value
                    );
                    break;
                default:
                    $value = preg_replace_callback(
                        '~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![.,:])~i',
                        function ($match) use ($protocol, &$links) {
                            return '<' . array_push(
                                $links,
                                "<a href=\"$protocol://{$match[1]}\">" . shorten_text(
                                    $match[1]
                                , $_ENV['MAX_LINE_LENGTH']) . "</a>"
                            ) . '>';
                        },
                        $value
                    );
                    break;
            }
        }

        // Insert all link
        return preg_replace_callback(
            '/<(\d+)>/',
            function ($match) use (&$links) {
                return $links[$match[1] - 1];
            },
            $value
        );
    }
}
