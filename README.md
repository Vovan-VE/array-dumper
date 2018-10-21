`array-dumper`
==============

[![Latest Stable Version](https://img.shields.io/packagist/v/vovan-ve/array-dumper.svg)](https://packagist.org/packages/vovan-ve/array-dumper)
[![Latest Dev Version](https://img.shields.io/packagist/vpre/vovan-ve/array-dumper.svg)](https://packagist.org/packages/vovan-ve/array-dumper)
[![Build Status](https://travis-ci.org/Vovan-VE/array-dumper.svg)](https://travis-ci.org/Vovan-VE/array-dumper)
[![License](https://poser.pugx.org/vovan-ve/array-dumper/license)](https://packagist.org/packages/vovan-ve/array-dumper)

Dumps PHP array into pretty printed PHP code. Only constant data can be dumped,
t.i. data, which can be used in `const` declaration since PHP 7.


Synopsis
--------

```php
use \VovanVE\array_dumper\ArrayDumper;

$dumper = new ArrayDumper();

$data = [
    'foo' => 42,
    'bar' => 'string',
    'list' => [10, 20, 30],
    'hash' => [
        'lorem' => 23,
        'ipsum' => true,
    ],
];

echo $dumper->dump($data);
```

Output:

```
[
    'foo' => 42,
    'bar' => 'string',
    'list' => [10, 20, 30],
    'hash' => [
        'lorem' => 23,
        'ipsum' => true,
    ],
]
```

Description
-----------

Simple array with sequential zero-based integer keys (aka lists) will be dumped
in single line, unless the line became too long with indention, or lists nesting
level became to deep (both are configurable).

An optional outer indent string can be supplied to `dump()` method. It is
used internally for nested arrays. It can be anything you want including
comment prefix `// `.

Installation
------------

Install through [composer][]:

    composer require vovan-ve/array-dumper

or add to `require` section in your composer.json:

    "vovan-ve/array-dumper": "~1.0.0"

License
-------

This package is under [MIT License][mit]


[composer]: http://getcomposer.org/
[mit]: https://opensource.org/licenses/MIT
