<?php
namespace VovanVE\array_dumper\helpers;

/**
 * Class StringHelper
 * @package VovanVE\array_dumper
 */
class StringHelper
{
    /**
     * Checks if a string is valid UTF-8 string
     * @param string $string Input string to test
     * @return bool Whether the input string is valid UTF-8 data
     */
    public static function isUtf8String(string $string): bool
    {
        // Keep calm. Just `mb_check_encoding($string, 'UTF-8')` sucks
        // PCRE `/u` is about 10 times faster.
        // https://3v4l.org/XYQ2A

        if (false !== \preg_match('/\\G/u', $string)) {
            return true;
        }
        if (\PREG_BAD_UTF8_ERROR === \preg_last_error()) {
            return false;
        }
        throw new \LogicException('Another unexpected failure happened with PCRE');
    }

    /**
     * Count string length in UTF-8 characters
     * @param string $string Input string
     * @return int String length in UTF-8 characters
     */
    public static function lengthUtf8(string $string): int
    {
        return \mb_strlen($string, 'UTF-8');
    }

    /**
     * Dump string to PHP string literal
     * @param string $value Input string
     * @return string PHP code of string literal. Doing `eval()` of this
     * code will return a string identical `===` to input string.
     */
    public static function dumpString(string $value): string
    {
        if ('' === $value) {
            return "''";
        }

        // non UTF-8 string will encode "\xFF" and require <">
        if (!self::isUtf8String($value)) {
            return '"' . \preg_replace_callback(
                // ---------------------------------------------------
                //     0x80 ..    0x7FF => C2 80       ... DF BF
                // ---------------------------------------------------
                //    0x800 ..    0xFFF => E0 A0 80    ... E0 BF BF
                //   0x1000 ..   0xFFFF => E1 80 80    ... EF BF BF
                // ---------------------------------------------------
                //  0x10000 ..  0x3FFFF => F0 90 80 80 ... F0 BF BF BF
                //  0x40000 ..  0xFFFFF => F1 80 80 80 ... F3 BF BF BF
                // 0x100000 .. 0x10FFFF => F4 80 80 80 ... F4 8F BF BF

                // " \x22
                // $ \x24
                // \ \x5C
                '/
                    \\G
                    (?:
                        [\\x20\\x21\\x23\\x25-\\x5B\\x5D-\\x7E]++
                        | [\\xC2-\\xDF] [\\x80-\\xBF]
                        | \\xE0 [\\xA0-\\xBF] [\\x80-\\xBF]
                        | [\\xE1-\\xEF] [\\x80-\\xBF]{2}
                        | \\xF0 [\\x90-\\xBF] [\\x80-\\xBF]{2}
                        | [\\xF1-\\xF3] [\\x80-\\xBF]{3}
                        | \\xF4 [\\x80-\\x8F] [\\x80-\\xBF]{2}
                    )*+
                    \\K
                    (?:
                        ( ["$\\\\] )
                        |
                        ( . )
                    )
                /xs',
                /** @uses _escapeStringCallback() */
                \Closure::fromCallable([__CLASS__, '_escapeStringCallback']),
                $value
            ) . '"';
        }

        // valid UTF-8 string with control ASCII characters
        // also will encode "\xFF" and require <">, but regexp is much simple
        if (\preg_match('/[\\x00-\\x1F\\x7F]/', $value)) {
            return '"' . \preg_replace_callback(
                '/(["$\\\\])|([\\x00-\\x1F\\x7F])/',
                /** @uses _escapeStringCallback() */
                \Closure::fromCallable([__CLASS__, '_escapeStringCallback']),
                $value
            ) . '"';
        }

        return \var_export($value, true);
    }

    private const ESCAPE_SPECIALS = [
        "\t" => '\\t',
        "\n" => '\\n',
        "\r" => '\\r',
        "\e" => '\\e',
    ];

    /**
     * Internal callback to escape characters
     * @param array $match A match from `preg_replace_callback()`
     * @return string
     */
    private static function _escapeStringCallback(array $match): string
    {
        if (isset($match[2])) {
            return self::ESCAPE_SPECIALS[$match[2]]
                ?? \sprintf('\\x%02X', \ord($match[2]));
        }
        return '\\' . $match[1];
    }
}
