<?php
namespace VovanVE\array_dumper;

use VovanVE\array_dumper\helpers\StringHelper;

class ArrayDumper
{
    public $indent = '    ';
    public $eol = "\n";
    public $lineLength = 100;
    public $listDepthLimit = 2;

    private $indentLength;

    public function dump(array $input, string $outerIndent = ''): string
    {
        $this->indentLength = StringHelper::lengthUtf8($this->indent);

        return $this->dumpArray(
            $input,
            $outerIndent,
            StringHelper::lengthUtf8($outerIndent)
        );
    }

    protected function dumpValue($value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (false === $value) {
            return 'false';
        }
        if (true === $value) {
            return 'true';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_string($value)) {
            return StringHelper::dumpString($value);
        }

        throw new \InvalidArgumentException('Unsupported data type:' . gettype($value));
    }

    protected function dumpArray(array $input, string $indent, int $outerLineLength): string
    {
        if (!$input) {
            return '[]';
        }

        if ($outerLineLength < $this->lineLength) {
            $dump = $this->tryDumpList($input, $outerLineLength, 1);
            if (null !== $dump) {
                return $dump;
            }
        }

        $next_indent = $indent . $this->indent;
        $next_indent_length = $outerLineLength + $this->indentLength;

        $dump = '[' . $this->eol;
        $index = 0;
        $still_linear = true;
        foreach ($input as $key => $value) {
            $item = $next_indent;
            $item_outer_length = $next_indent_length;

            if ($still_linear && $key === $index) {
                ++$index;
            } else {
                $still_linear = false;

                $key_dump = $this->dumpValue($key);
                $item .= $key_dump . ' => ';
                $item_outer_length += 4 + StringHelper::lengthUtf8($key_dump);
            }

            if (is_array($value)) {
                // there will be trailing comma ',' after value, so outer length gives +1
                $value_dump = $this->dumpArray($value, $next_indent, $item_outer_length + 1);
            } else {
                $value_dump = $this->dumpValue($value);
            }

            $item .= $value_dump;

            $dump .= $item . ',' . $this->eol;
        }

        $dump .= $indent . ']';

        return $dump;
    }

    protected function tryDumpList(array $input, int $outerLineLength, int $level): ?string
    {
        if ($level > $this->listDepthLimit) {
            return null;
        }

        $dump = '[';
        $total_length = $outerLineLength + 1;

        $index = 0;
        foreach ($input as $key => $value) {
            if ($key !== $index) {
                return null;
            }

            if ($index > 0) {
                $dump .= ', ';
                $total_length += 2;
            }

            if ($total_length >= $this->lineLength) {
                return null;
            }

            ++$index;

            if (is_array($value)) {
                $value_dump = $this->tryDumpList($value, $total_length, $level + 1);
                if (null === $value_dump) {
                    return null;
                }
            } else {
                $value_dump = $this->dumpValue($value);
            }

            $total_length += StringHelper::lengthUtf8($value_dump);
            if ($total_length >= $this->lineLength) {
                return null;
            }

            $dump .= $value_dump;
        }

        $total_length += 1;
        if ($total_length > $this->lineLength) {
            return null;
        }

        $dump .= ']';
        return $dump;
    }
}
