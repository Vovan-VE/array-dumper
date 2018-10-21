<?php
namespace VovanVE\array_dumper\tests\unit\helpers;

use VovanVE\array_dumper\helpers\StringHelper;
use VovanVE\array_dumper\tests\helpers\BaseTestCase;

class StringHelperTest extends BaseTestCase
{
    /**
     * @param bool $is
     * @param string $string
     * @dataProvider isUtf8String_dataProvider
     */
    public function testIsUtf8String(bool $is, string $string): void
    {
        $this->assertEquals($is, StringHelper::isUtf8String($string));
    }

    public function isUtf8String_dataProvider(): array
    {
        return [
            [true, ""],
            [true, "foo bar"],
            [true, "\x00 \x7F \u{80} \u{7FF} \u{800} \u{FFFF} \u{10000} \u{10FFFF}"],

            [false, "foo \x80 bar"],
            [false, "foo \xBF bar"],

            [false, "foo \xC2\x00 bar"],
            [false, "foo \xC2\x40 bar"],
            [false, "foo \xC2\xC2 bar"],
            [false, "foo \xC2\u{80} bar"],
            [false, "foo \xC2\u{800} bar"],
            [false, "foo \xC2\u{10000} bar"],
            [false, "foo \xC2\u{10FFFF} bar"],

            [false, "foo \xE0\x80\x80 bar"],
            [false, "foo \xE0\xBF bar"],
            [false, "foo \xE0\xBF\xBF\xBF bar"],
            [false, "foo \xE0\xFF bar"],
            [false, "foo \xE0\xFF\xFF bar"],

            [false, "foo \xF0\x80\x80\x80 bar"],
            [false, "foo \xF0\x90\x80 bar"],
            [false, "foo \xF0\x90\x80\x80\x80 bar"],
            [false, "foo \xF4\x90\x80\x80 bar"],

            [false, "foo \xF8\x88\x80\x80\x80 bar"],
        ];
    }

    /**
     * @param int $length
     * @param string $string
     * @dataProvider lengthUtf8_dataProvider
     */
    public function testLengthUtf8(int $length, string $string): void
    {
        $this->assertEquals($length, StringHelper::lengthUtf8($string), 'single');
        $this->assertEquals($length * 2, StringHelper::lengthUtf8($string . $string), 'double');
        $this->assertEquals($length + 2, StringHelper::lengthUtf8("-$string-"), 'wrap');
    }

    public function lengthUtf8_dataProvider(): array
    {
        return [
            [0, ""],
            [1, "\x00"],
            [1, "\x7F"],
            [1, "\u{80}"],
            [1, "\u{7FF}"],
            [1, "\u{800}"],
            [1, "\u{FFFF}"],
            [1, "\u{10000}"],
            [1, "\u{10FFFF}"],
        ];
    }

    /**
     * @param string $expect
     * @param string $input
     * @dataProvider dumpString_dataProvider
     */
    public function testDumpString(string $expect, string $input): void
    {
        $dump = StringHelper::dumpString($input);
        $this->assertEquals($expect, $dump, 'code');
        $this->assertEquals($input, eval("return $dump;"), 'eval');
    }

    public function dumpString_dataProvider(): array
    {
        return [
            ["''", ""],
            ["'\\''", "'"],
            ["'\"'", '"'],
            ["'\$'", '$'],
            ["'foo'", "foo"],
            ["'№Ё'", "№Ё"],
            ["'foo\\'bar\\\\baz'", "foo'bar\\baz"],
            ['"foo\\nbar\\t\\$baz"', "foo\nbar\t\$baz"],
            ['"foo\\nbar\\xFF\\$baz"', "foo\nbar\xFF\$baz"],

            ['".\\x00."', ".\x00."],
            ['".\\x01."', ".\x01."],
            ['".\\x02."', ".\x02."],
            ['".\\x03."', ".\x03."],
            ['".\\x04."', ".\x04."],
            ['".\\x05."', ".\x05."],
            ['".\\x06."', ".\x06."],
            ['".\\x07."', ".\x07."],
            ['".\\x08."', ".\x08."],
            ['".\\t."', ".\x09."],
            ['".\\n."', ".\x0A."],
            ['".\\x0B."', ".\x0B."],
            ['".\\x0C."', ".\x0C."],
            ['".\\r."', ".\x0D."],
            ['".\\x0E."', ".\x0E."],
            ['".\\x0F."', ".\x0F."],
            ['".\\x10."', ".\x10."],
            ['".\\x11."', ".\x11."],
            ['".\\x12."', ".\x12."],
            ['".\\x13."', ".\x13."],
            ['".\\x14."', ".\x14."],
            ['".\\x15."', ".\x15."],
            ['".\\x16."', ".\x16."],
            ['".\\x17."', ".\x17."],
            ['".\\x18."', ".\x18."],
            ['".\\x19."', ".\x19."],
            ['".\\x1A."', ".\x1A."],
            ['".\\e."', ".\x1B."],
            ['".\\x1C."', ".\x1C."],
            ['".\\x1D."', ".\x1D."],
            ['".\\x1E."', ".\x1E."],
            ['".\\x1F."', ".\x1F."],
            ['".\\x7F."', ".\x7F."],
            ['".\\x80."', ".\x80."],
            ['".\\xFF."', ".\xFF."],

            ['".№.\\xFF.№."', ".№.\xFF.№."],
        ];
    }
}
