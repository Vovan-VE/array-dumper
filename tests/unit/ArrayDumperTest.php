<?php
namespace VovanVE\array_dumper\tests\unit;

use VovanVE\array_dumper\ArrayDumper;
use VovanVE\array_dumper\tests\helpers\BaseTestCase;

class ArrayDumperTest extends BaseTestCase
{
    /**
     * @param string $expected
     * @param array $input
     * @dataProvider dump_dataProvider
     */
    public function testDump(string $expected, array $input): void
    {
        $dump = (new ArrayDumper)->dump($input);
        $this->assertEquals($expected, $dump, 'code');
        $this->assertSame($input, eval("return $dump;"), 'eval');
    }

    public function dump_dataProvider(): array
    {
        return [
            ['[]', []],
            ['[null]', [null]],
            ['[false]', [false]],
            ['[true]', [true]],
            ['[42]', [42]],
            ["['string']", ['string']],
            ['[[]]', [[]]],

            [
                "[null, false, true, 42, 'string']",
                [null, false, true, 42, 'string'],
            ],

            [
                "['" . str_repeat('.', 96) . "']",
                [str_repeat('.', 96)],
            ],
            [
                join("\n", [
                    "[",
                    "    '" . str_repeat('.', 97) . "',",
                    "]",
                ]),
                [str_repeat('.', 97)],
            ],

            [
                "[['" . str_repeat('.', 94) . "']]",
                [[str_repeat('.', 94)]],
            ],
            [
                join("\n", [
                    "[",
                    "    [",
                    "        '" . str_repeat('.', 95) . "',",
                    "    ],",
                    "]",
                ]),
                [[str_repeat('.', 95)]],
            ],

            [
                join("\n", [
                    "[",
                    "    'foo' => 42,",
                    "    'bar' => 'string',",
                    "    'baz' => [10, 20, 30],",
                    "    'qux' => [",
                    "        'lorem' => true,",
                    "        'ipsum' => false,",
                    "    ],",
                    "    'nested-lists' => [",
                    "        [",
                    "            [[10, 20, 30], [40, 50, 60]],",
                    "            [[70, 80, 90], [42, 37, 23]],",
                    "        ],",
                    "    ],",
                    "    'list-of-hash' => [",
                    "        [",
                    "            'x' => 42,",
                    "            'y' => 37,",
                    "        ],",
                    "        [",
                    "            'a' => 23,",
                    "            'b' => 19,",
                    "        ],",
                    "    ],",
                    "]",
                ]),
                [
                    'foo' => 42,
                    'bar' => 'string',
                    'baz' => [10, 20, 30],
                    'qux' => [
                        'lorem' => true,
                        'ipsum' => false,
                    ],
                    'nested-lists' => [
                        [
                            [[10, 20, 30], [40, 50, 60]],
                            [[70, 80, 90], [42, 37, 23]],
                        ],
                    ],
                    'list-of-hash' => [
                        [
                            'x' => 42,
                            'y' => 37,
                        ],
                        [
                            'a' => 23,
                            'b' => 19,
                        ],
                    ],
                ]
            ],
        ];
    }

    public function testDumpCommented(): void
    {
        $dumper = new ArrayDumper();

        $array = [
            'foo' => 42,
            'bar' => 'string',
            'baz' => [10, 20, 30],
            'qux' => [
                'lorem' => true,
                'ipsum' => false,
            ],
            'nested-lists' => [
                [
                    [[10, 20, 30], [40, 50, 60]],
                    [[70, 80, 90], [42, 37, 23]],
                ],
            ],
            'list-of-hash' => [
                [
                    'x' => 42,
                    'y' => 37,
                ],
                [
                    'a' => 23,
                    'b' => 19,
                ],
            ],
        ];
        $expect = <<<DUMP
/**
 * [
 *     'foo' => 42,
 *     'bar' => 'string',
 *     'baz' => [10, 20, 30],
 *     'qux' => [
 *         'lorem' => true,
 *         'ipsum' => false,
 *     ],
 *     'nested-lists' => [
 *         [
 *             [[10, 20, 30], [40, 50, 60]],
 *             [[70, 80, 90], [42, 37, 23]],
 *         ],
 *     ],
 *     'list-of-hash' => [
 *         [
 *             'x' => 42,
 *             'y' => 37,
 *         ],
 *         [
 *             'a' => 23,
 *             'b' => 19,
 *         ],
 *     ],
 * ]
 */
DUMP;

        // if you are going to do so, notice, that
        // string "foo */ bar" is source data
        // will break you cool comment
        // unless you split "*/"
        $actual = <<<COMMENT
/**
 * {$dumper->dump($array, ' * ')}
 */
COMMENT;

        $this->assertEquals($expect, $actual);
    }
}
